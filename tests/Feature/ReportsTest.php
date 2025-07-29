<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\Payslip;
use Livewire\Livewire;
use App\Livewire\Admin\Reports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee1;
    protected $employee2;
    protected $employee3;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create employees in different departments
        $this->employee1 = User::factory()->create([
            'role' => 'employee',
            'department' => 'Engineering',
            'status' => 'active',
            'hourly_rate' => 25.00,
        ]);

        $this->employee2 = User::factory()->create([
            'role' => 'employee',
            'department' => 'Marketing',
            'status' => 'active',
            'hourly_rate' => 30.00,
        ]);

        $this->employee3 = User::factory()->create([
            'role' => 'employee',
            'department' => 'Engineering',
            'status' => 'active',
            'hourly_rate' => 28.00,
        ]);
    }

    /** @test */
    public function admin_can_view_reports_page()
    {
        $this->actingAs($this->admin)
            ->get('/admin/reports')
            ->assertStatus(200)
            ->assertSee('Advanced Reports');
    }

    /** @test */
    public function non_admin_cannot_access_reports()
    {
        $this->actingAs($this->employee1)
            ->get('/admin/reports')
            ->assertRedirect('/');
    }

    /** @test */
    public function reports_component_loads_with_default_settings()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertSet('selectedReport', 'payroll')
            ->assertSet('reportFormat', 'web');
    }

    /** @test */
    public function payroll_report_calculates_correctly()
    {
        // Create time records for employees
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
            'overtime_hours' => 2.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now(),
            'total_hours' => 7.5,
            'overtime_hours' => 0.5,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class);

        $payrollReport = $component->call('getPayrollReportProperty');

        // Check employee 1 calculations
        $employee1Report = collect($payrollReport)->firstWhere('employee', $this->employee1->name);
        $this->assertEquals(8.0, $employee1Report['total_hours']);
        $this->assertEquals(6.0, $employee1Report['regular_hours']); // 8 - 2 overtime
        $this->assertEquals(2.0, $employee1Report['overtime_hours']);
        $this->assertEquals(150.00, $employee1Report['regular_pay']); // 6 * $25
        $this->assertEquals(75.00, $employee1Report['overtime_pay']); // 2 * $25 * 1.5
        $this->assertEquals(225.00, $employee1Report['total_pay']);

        // Check employee 2 calculations
        $employee2Report = collect($payrollReport)->firstWhere('employee', $this->employee2->name);
        $this->assertEquals(7.5, $employee2Report['total_hours']);
        $this->assertEquals(7.0, $employee2Report['regular_hours']); // 7.5 - 0.5 overtime
        $this->assertEquals(0.5, $employee2Report['overtime_hours']);
        $this->assertEquals(210.00, $employee2Report['regular_pay']); // 7 * $30
        $this->assertEquals(22.50, $employee2Report['overtime_pay']); // 0.5 * $30 * 1.5
        $this->assertEquals(232.50, $employee2Report['total_pay']);
    }

    /** @test */
    public function time_analytics_report_works()
    {
        // Create time records for testing with different dates
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
            'overtime_hours' => 2.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now()->subDays(2),
            'total_hours' => 6.0,
            'overtime_hours' => 0.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now()->subDays(1),
            'total_hours' => 7.5,
            'overtime_hours' => 0.5,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now()->subDays(3),
            'total_hours' => 6.0,
            'overtime_hours' => 0.0,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class);

        $timeAnalytics = $component->get('timeAnalytics');

        // Check that we get paginated results
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $timeAnalytics);
        $this->assertGreaterThan(0, $timeAnalytics->count());
        
        // Check that the data contains the expected records
        $this->assertTrue($timeAnalytics->contains('user_id', $this->employee1->id));
        $this->assertTrue($timeAnalytics->contains('user_id', $this->employee2->id));
    }

    /** @test */
    public function department_report_works()
    {
        // Create time records for employees in different departments
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
            'overtime_hours' => 2.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee3->id,
            'date' => now(),
            'total_hours' => 7.5,
            'overtime_hours' => 0.5,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now(),
            'total_hours' => 8.0,
            'overtime_hours' => 0.0,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class);

        $departmentReport = $component->call('getDepartmentReportProperty');

        // Check Engineering department
        $engineeringReport = collect($departmentReport)->firstWhere('department', 'Engineering');
        $this->assertEquals(2, $engineeringReport['employee_count']);
        $this->assertEquals(2, $engineeringReport['active_employees']);
        $this->assertEquals(15.5, $engineeringReport['total_hours']);
        $this->assertEquals(2.5, $engineeringReport['overtime_hours']);
        $this->assertEquals(7.75, $engineeringReport['average_hours_per_employee']);

        // Check Marketing department
        $marketingReport = collect($departmentReport)->firstWhere('department', 'Marketing');
        $this->assertEquals(1, $marketingReport['employee_count']);
        $this->assertEquals(1, $marketingReport['active_employees']);
        $this->assertEquals(8.0, $marketingReport['total_hours']);
        $this->assertEquals(0.0, $marketingReport['overtime_hours']);
        $this->assertEquals(8.0, $marketingReport['average_hours_per_employee']);
    }

    /** @test */
    public function attendance_report_works()
    {
        // Create time records across a week
        for ($i = 0; $i < 5; $i++) {
            TimeRecord::factory()->create([
                'user_id' => $this->employee1->id,
                'date' => now()->subDays($i),
                'total_hours' => 8.0,
            ]);
        }

        // Employee 2 only worked 3 days
        for ($i = 0; $i < 3; $i++) {
            TimeRecord::factory()->create([
                'user_id' => $this->employee2->id,
                'date' => now()->subDays($i),
                'total_hours' => 7.5,
            ]);
        }

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('dateFrom', now()->subDays(6)->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'));

        $attendanceReport = $component->call('getAttendanceReportProperty');

        // Check employee 1 attendance
        $employee1Report = collect($attendanceReport)->firstWhere('employee', $this->employee1->name);
        $this->assertEquals(7, $employee1Report['total_days']); // 7 days in range
        $this->assertEquals(5, $employee1Report['days_worked']);
        $this->assertEquals(2, $employee1Report['days_absent']);
        $this->assertEquals(71.43, round($employee1Report['attendance_rate'], 2));
        $this->assertEquals(40.0, $employee1Report['total_hours']);
        $this->assertEquals(8.0, $employee1Report['average_hours_per_day']);

        // Check employee 2 attendance
        $employee2Report = collect($attendanceReport)->firstWhere('employee', $this->employee2->name);
        $this->assertEquals(7, $employee2Report['total_days']);
        $this->assertEquals(3, $employee2Report['days_worked']);
        $this->assertEquals(4, $employee2Report['days_absent']);
        $this->assertEquals(42.86, round($employee2Report['attendance_rate'], 2));
        $this->assertEquals(22.5, $employee2Report['total_hours']);
        $this->assertEquals(7.5, $employee2Report['average_hours_per_day']);
    }

    /** @test */
    public function can_filter_reports_by_department()
    {
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now(),
            'total_hours' => 7.5,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('selectedDepartment', 'Engineering');

        $payrollReport = $component->call('getPayrollReportProperty');
        
        // Should only include Engineering employees
        $this->assertCount(2, $payrollReport); // employee1 and employee3
        $this->assertContains($this->employee1->name, collect($payrollReport)->pluck('employee'));
        $this->assertContains($this->employee3->name, collect($payrollReport)->pluck('employee'));
        $this->assertNotContains($this->employee2->name, collect($payrollReport)->pluck('employee'));
    }

    /** @test */
    public function can_filter_reports_by_employee()
    {
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now(),
            'total_hours' => 7.5,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('selectedEmployee', $this->employee1->id);

        $payrollReport = $component->call('getPayrollReportProperty');
        
        // Should only include selected employee
        $this->assertCount(1, $payrollReport);
        $this->assertEquals($this->employee1->name, $payrollReport[0]['employee']);
    }

    /** @test */
    public function can_filter_reports_by_date_range()
    {
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'total_hours' => 8.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now()->subDays(10),
            'total_hours' => 7.5,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('dateFrom', now()->subDays(5)->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'));

        $payrollReport = $component->call('getPayrollReportProperty');
        
        // Should only include records within date range
        $this->assertCount(1, $payrollReport);
        $this->assertEquals($this->employee1->name, $payrollReport[0]['employee']);
    }

    /** @test */
    public function can_change_report_type()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('selectedReport', 'time_analytics')
            ->assertSet('selectedReport', 'time_analytics')
            ->set('selectedReport', 'department')
            ->assertSet('selectedReport', 'department')
            ->set('selectedReport', 'attendance')
            ->assertSet('selectedReport', 'attendance');
    }

    /** @test */
    public function can_change_export_format()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->set('reportFormat', 'excel')
            ->assertSet('reportFormat', 'excel')
            ->set('reportFormat', 'pdf')
            ->assertSet('reportFormat', 'pdf');
    }

    /** @test */
    public function employees_list_is_populated()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertSee($this->employee1->name)
            ->assertSee($this->employee2->name)
            ->assertSee($this->employee3->name);
    }

    /** @test */
    public function departments_list_is_populated()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertSee('Engineering')
            ->assertSee('Marketing');
    }

    /** @test */
    public function generate_report_function_works()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->call('generateReport')
            ->assertSee('Report generated successfully!');
    }

    /** @test */
    public function export_report_function_works()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->call('exportReport')
            ->assertSee('Report exported successfully!');
    }
} 