<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class Reports extends Component
{
    use WithPagination;

    public $selectedReport = 'payroll';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedDepartment = '';
    public $selectedEmployee = '';
    public $reportFormat = 'web';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSelectedReport()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedEmployee()
    {
        $this->resetPage();
    }

    public function generateReport()
    {
        // This would trigger report generation based on selected type
        session()->flash('message', 'Report generated successfully!');
    }

    public function exportReport()
    {
        // This would export the report in the selected format
        session()->flash('message', 'Report exported successfully!');
    }

    public function getPayrollReportProperty()
    {
        $query = User::where('role', 'employee')
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->when($this->selectedEmployee, function ($query) {
                $query->where('id', $this->selectedEmployee);
            });

        $employees = $query->get();

        $report = [];
        foreach ($employees as $employee) {
            $timeRecords = TimeRecord::where('user_id', $employee->id)
                ->when($this->dateFrom, function ($query) {
                    $query->where('date', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->where('date', '<=', $this->dateTo);
                })
                ->get();

            $totalHours = $timeRecords->sum('total_hours');
            $overtimeHours = $timeRecords->sum('overtime_hours');
            $regularHours = $totalHours - $overtimeHours;
            
            $regularPay = $regularHours * $employee->hourly_rate;
            $overtimePay = $overtimeHours * ($employee->hourly_rate * 1.5);
            $totalPay = $regularPay + $overtimePay;

            $report[] = [
                'employee' => $employee->name,
                'department' => $employee->department,
                'hourly_rate' => $employee->hourly_rate,
                'total_hours' => $totalHours,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'regular_pay' => $regularPay,
                'overtime_pay' => $overtimePay,
                'total_pay' => $totalPay,
            ];
        }

        // Manually paginate the collection
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = collect($report)->slice($offset, $perPage);
        
        return new LengthAwarePaginator(
            $items,
            count($report),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getTimeAnalyticsProperty()
    {
        $query = TimeRecord::with('user')
            ->when($this->selectedDepartment, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('department', $this->selectedDepartment);
                });
            })
            ->when($this->selectedEmployee, function ($query) {
                $query->where('user_id', $this->selectedEmployee);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('date', '<=', $this->dateTo);
            })
            ->orderBy('date', 'desc');

        return $query->paginate(15);
    }

    public function getDepartmentReportProperty()
    {
        $departments = User::where('role', 'employee')
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();

        $report = [];
        foreach ($departments as $department) {
            $employees = User::where('role', 'employee')
                ->where('department', $department)
                ->when($this->selectedEmployee, function ($query) {
                    $query->where('id', $this->selectedEmployee);
                })
                ->get();

            $employeeIds = $employees->pluck('id');
            
            $timeRecords = TimeRecord::whereIn('user_id', $employeeIds)
                ->when($this->dateFrom, function ($query) {
                    $query->where('date', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->where('date', '<=', $this->dateTo);
                })
                ->get();

            $totalHours = $timeRecords->sum('total_hours');
            $overtimeHours = $timeRecords->sum('overtime_hours');
            $totalSpending = $employees->sum(function ($employee) use ($timeRecords) {
                $employeeRecords = $timeRecords->where('user_id', $employee->id);
                $regularHours = $employeeRecords->sum('total_hours') - $employeeRecords->sum('overtime_hours');
                $overtimeHours = $employeeRecords->sum('overtime_hours');
                return ($regularHours * $employee->hourly_rate) + ($overtimeHours * $employee->hourly_rate * 1.5);
            });

            $report[] = [
                'department' => $department,
                'employee_count' => $employees->count(),
                'active_employees' => $employees->where('status', 'active')->count(),
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'average_hours_per_employee' => $employees->count() > 0 ? round($totalHours / $employees->count(), 2) : 0,
                'total_spending' => $totalSpending,
                'average_spending_per_employee' => $employees->count() > 0 ? round($totalSpending / $employees->count(), 2) : 0,
            ];
        }

        // Manually paginate the collection
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = collect($report)->slice($offset, $perPage);
        
        return new LengthAwarePaginator(
            $items,
            count($report),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getAttendanceReportProperty()
    {
        $query = User::where('role', 'employee')
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->when($this->selectedEmployee, function ($query) {
                $query->where('id', $this->selectedEmployee);
            });

        $employees = $query->get();

        $report = [];
        foreach ($employees as $employee) {
            $timeRecords = TimeRecord::where('user_id', $employee->id)
                ->when($this->dateFrom, function ($query) {
                    $query->where('date', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->where('date', '<=', $this->dateTo);
                })
                ->get();

            $totalDays = Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1;
            $daysWorked = $timeRecords->count();
            $daysAbsent = $totalDays - $daysWorked;
            $attendanceRate = $totalDays > 0 ? round(($daysWorked / $totalDays) * 100, 2) : 0;

            $report[] = [
                'employee' => $employee->name,
                'department' => $employee->department,
                'total_days' => $totalDays,
                'days_worked' => $daysWorked,
                'days_absent' => $daysAbsent,
                'attendance_rate' => $attendanceRate,
                'total_hours' => $timeRecords->sum('total_hours'),
                'average_hours_per_day' => $daysWorked > 0 ? round($timeRecords->sum('total_hours') / $daysWorked, 2) : 0,
            ];
        }

        // Manually paginate the collection
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = collect($report)->slice($offset, $perPage);
        
        return new LengthAwarePaginator(
            $items,
            count($report),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getEmployeesProperty()
    {
        return User::where('role', 'employee')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getDepartmentsProperty()
    {
        return User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();
    }

    public function render()
    {
        return view('livewire.admin.reports')
            ->layout('layouts.app');
    }
} 