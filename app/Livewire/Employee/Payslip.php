<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Payslip as PayslipModel;
use Carbon\Carbon;

class Payslip extends Component
{
    use WithPagination;

    public $selectedMonth;
    public $selectedYear;
    public $showPayslipModal = false;
    public $selectedPayslip;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function viewPayslip($payslipId)
    {
        $this->selectedPayslip = PayslipModel::where('id', $payslipId)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($this->selectedPayslip) {
            $this->showPayslipModal = true;
        }
    }

    public function closePayslipModal()
    {
        $this->showPayslipModal = false;
        $this->selectedPayslip = null;
    }

    public function downloadPayslip($payslipId)
    {
        $payslip = PayslipModel::where('id', $payslipId)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($payslip) {
            // TODO: Implement PDF download functionality
            session()->flash('message', 'Payslip download feature coming soon!');
        }
    }

    public function getPayslipsProperty()
    {
        return PayslipModel::where('user_id', auth()->user()->id)
            ->when($this->selectedMonth, function ($query) {
                $query->where('month', $this->selectedMonth);
            })
            ->when($this->selectedYear, function ($query) {
                $query->where('year', $this->selectedYear);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);
    }

    public function getPayslipStatsProperty()
    {
        $totalPayslips = PayslipModel::where('user_id', auth()->user()->id)->count();
        $totalEarnings = PayslipModel::where('user_id', auth()->user()->id)->sum('total_pay');
        $totalDeductions = PayslipModel::where('user_id', auth()->user()->id)->sum('deductions');
        $totalNetPay = PayslipModel::where('user_id', auth()->user()->id)->sum('net_pay');

        return [
            'total_payslips' => $totalPayslips,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'total_net_pay' => $totalNetPay,
        ];
    }

    public function getMonthsProperty()
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    public function getYearsProperty()
    {
        $currentYear = now()->year;
        $years = [];
        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
            $years[$i] = $i;
        }
        return $years;
    }

    public function render()
    {
        return view('livewire.employee.payslip')
            ->layout('layouts.app');
    }
} 