<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LeaveRequest;
use Livewire\Livewire;
use App\Livewire\Employee\LeaveRequest as EmployeeLeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeLeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@dtr.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'department' => 'IT',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function employee_can_view_leave_request_page()
    {
        $response = $this->actingAs($this->employee)->get('/employee/leave-requests');
        $response->assertStatus(200);
        $response->assertSee('Leave Requests');
    }

    /** @test */
    public function non_employee_cannot_access_leave_request_page()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@dtr.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/employee/leave-requests');
        $response->assertStatus(302); // Redirect instead of 403
    }

    /** @test */
    public function leave_request_component_loads_without_requests()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->assertSee('Leave Requests');
        $component->assertSee('No leave requests');
    }

    /** @test */
    public function can_open_leave_request_modal()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        
        $this->assertTrue($component->get('showModal'));
        $this->assertFalse($component->get('editing'));
    }

    /** @test */
    public function can_submit_leave_request()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', 'vacation');
        $component->set('start_date', '2025-12-01');
        $component->set('end_date', '2025-12-03');
        $component->set('reason', 'Family vacation');

        $component->call('save');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'leave_type' => 'vacation',
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-03',
            'days_requested' => 3,
            'reason' => 'Family vacation',
            'status' => 'pending',
        ]);

        $component->assertSee('Leave request submitted successfully!');
    }

    /** @test */
    public function leave_request_validation_works()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', '');
        $component->set('start_date', '');
        $component->set('end_date', '');
        $component->set('reason', '');

        $component->call('save');

        $component->assertHasErrors(['leave_type', 'start_date', 'end_date', 'reason']);
    }

    /** @test */
    public function start_date_must_be_after_or_equal_to_today()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', 'vacation');
        $component->set('start_date', '2023-01-01'); // Past date
        $component->set('end_date', '2023-01-03');
        $component->set('reason', 'Test reason');

        $component->call('save');

        $component->assertHasErrors(['start_date']);
    }

    /** @test */
    public function end_date_must_be_after_or_equal_to_start_date()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', 'vacation');
        $component->set('start_date', '2025-12-03');
        $component->set('end_date', '2025-12-01'); // Before start date
        $component->set('reason', 'Test reason');

        $component->call('save');

        $component->assertHasErrors(['end_date']);
    }

    /** @test */
    public function days_requested_is_calculated_automatically()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('start_date', '2025-12-01');
        $component->set('end_date', '2025-12-05');

        $this->assertEquals(5, $component->get('days_requested'));
    }

    /** @test */
    public function can_cancel_pending_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->pending()->create([
            'user_id' => $this->employee->id
        ]);

        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('cancelRequest', $leaveRequest->id);

        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id
        ]);

        $component->assertSee('Leave request cancelled successfully!');
    }

    /** @test */
    public function cannot_cancel_approved_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->approved()->create([
            'user_id' => $this->employee->id
        ]);

        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('cancelRequest', $leaveRequest->id);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id
        ]);

        $component->assertSee('Cannot cancel this leave request.');
    }

    /** @test */
    public function leave_stats_are_calculated_correctly()
    {
        LeaveRequest::factory()->pending()->count(2)->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->approved()->count(3)->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->rejected()->count(1)->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $stats = $component->get('leaveStats');
        
        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(3, $stats['approved']);
        $this->assertEquals(1, $stats['rejected']);
    }

    /** @test */
    public function pagination_works_for_leave_requests()
    {
        LeaveRequest::factory()->count(15)->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $leaveRequests = $component->get('leaveRequests');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $leaveRequests);
        $this->assertEquals(10, $leaveRequests->perPage());
        $this->assertEquals(15, $leaveRequests->total());
    }

    /** @test */
    public function employee_can_only_see_their_own_leave_requests()
    {
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        
        LeaveRequest::factory()->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->create(['user_id' => $otherEmployee->id]);

        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $leaveRequests = $component->get('leaveRequests');
        
        $this->assertEquals(1, $leaveRequests->count());
        $this->assertEquals($this->employee->id, $leaveRequests->first()->user_id);
    }

    /** @test */
    public function modal_closes_after_successful_submission()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', 'vacation');
        $component->set('start_date', '2025-12-01');
        $component->set('end_date', '2025-12-03');
        $component->set('reason', 'Test reason');

        $component->call('save');

        $this->assertFalse($component->get('showModal'));
    }

    /** @test */
    public function form_resets_after_submission()
    {
        $component = Livewire::actingAs($this->employee)
            ->test(EmployeeLeaveRequest::class);

        $component->call('openModal');
        $component->set('leave_type', 'vacation');
        $component->set('start_date', '2025-12-01');
        $component->set('end_date', '2025-12-03');
        $component->set('reason', 'Test reason');

        $component->call('save');

        $this->assertEquals('', $component->get('leave_type'));
        $this->assertEquals(now()->format('Y-m-d'), $component->get('start_date'));
        $this->assertEquals(now()->format('Y-m-d'), $component->get('end_date'));
        $this->assertEquals('', $component->get('reason'));
    }
}
