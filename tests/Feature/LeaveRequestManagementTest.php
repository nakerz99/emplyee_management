<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LeaveRequest;
use Livewire\Livewire;
use App\Livewire\Admin\LeaveRequestManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaveRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@dtr.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

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
    public function admin_can_view_leave_request_management_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/leave-requests');
        $response->assertStatus(200);
        $response->assertSee('Leave Request Management');
    }

    /** @test */
    public function non_admin_cannot_access_leave_request_management()
    {
        $response = $this->actingAs($this->employee)->get('/admin/leave-requests');
        $response->assertStatus(302); // Redirect instead of 403
    }

    /** @test */
    public function leave_request_management_component_loads_without_requests()
    {
        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->assertSee('Leave Request Management');
        $component->assertSee('No leave requests found');
    }

    /** @test */
    public function can_filter_leave_requests_by_employee()
    {
        // Create leave requests for different employees
        $employee2 = User::factory()->create(['role' => 'employee']);
        
        LeaveRequest::factory()->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->create(['user_id' => $employee2->id]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        // Refresh the component to get updated data
        $component->set('selectedEmployee', $this->employee->id);
        $component->call('$refresh');
        
        $leaveRequests = $component->get('leaveRequests');
        $this->assertEquals(1, $leaveRequests->count());
        $this->assertEquals($this->employee->id, $leaveRequests->first()->user_id);
    }

    /** @test */
    public function can_filter_leave_requests_by_status()
    {
        LeaveRequest::factory()->pending()->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->approved()->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->set('selectedStatus', 'pending');
        $component->call('$refresh');
        
        $leaveRequests = $component->get('leaveRequests');
        $this->assertEquals(1, $leaveRequests->count());
        $this->assertEquals('pending', $leaveRequests->first()->status);
    }

    /** @test */
    public function can_filter_leave_requests_by_leave_type()
    {
        LeaveRequest::factory()->vacation()->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->sick()->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->set('selectedLeaveType', 'vacation');
        $component->call('$refresh');
        
        $leaveRequests = $component->get('leaveRequests');
        $this->assertEquals(1, $leaveRequests->count());
        $this->assertEquals('vacation', $leaveRequests->first()->leave_type);
    }

    /** @test */
    public function can_approve_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->pending()->create([
            'user_id' => $this->employee->id
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->call('openApprovalModal', $leaveRequest->id, 'approve');
        $component->set('adminNotes', 'Approved for vacation');
        $component->call('approveRequest');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'admin_notes' => 'Approved for vacation',
        ]);

        $component->assertSee('Leave request approved successfully!');
    }

    /** @test */
    public function can_reject_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->pending()->create([
            'user_id' => $this->employee->id
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->call('openApprovalModal', $leaveRequest->id, 'reject');
        $component->set('adminNotes', 'Insufficient notice period');
        $component->call('rejectRequest');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'approved_by' => $this->admin->id,
            'admin_notes' => 'Insufficient notice period',
        ]);

        $component->assertSee('Leave request rejected successfully!');
    }

    /** @test */
    public function can_bulk_approve_pending_requests()
    {
        LeaveRequest::factory()->pending()->count(3)->create([
            'user_id' => $this->employee->id
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $component->call('bulkApprove');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'status' => 'approved',
            'admin_notes' => 'Bulk approved by admin',
        ]);

        $component->assertSee('3 leave requests approved successfully!');
    }

    /** @test */
    public function summary_stats_are_calculated_correctly()
    {
        LeaveRequest::factory()->pending()->count(2)->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->approved()->count(3)->create(['user_id' => $this->employee->id]);
        LeaveRequest::factory()->rejected()->count(1)->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $stats = $component->get('summaryStats');
        
        $this->assertEquals(6, $stats['total_requests']);
        $this->assertEquals(2, $stats['pending_requests']);
        $this->assertEquals(3, $stats['approved_requests']);
        $this->assertEquals(1, $stats['rejected_requests']);
        $this->assertGreaterThan(0, $stats['total_days']);
    }

    /** @test */
    public function pagination_works_for_leave_requests()
    {
        LeaveRequest::factory()->count(20)->create(['user_id' => $this->employee->id]);

        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $leaveRequests = $component->get('leaveRequests');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $leaveRequests);
        $this->assertEquals(15, $leaveRequests->perPage());
        $this->assertEquals(20, $leaveRequests->total());
    }

    /** @test */
    public function employees_list_is_populated()
    {
        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $employees = $component->get('employees');
        
        $this->assertTrue($employees->contains($this->employee));
        $this->assertFalse($employees->contains($this->admin)); // Admin should not be in employee list
    }

    /** @test */
    public function departments_list_is_populated()
    {
        $component = Livewire::actingAs($this->admin)
            ->test(LeaveRequestManagement::class);

        $departments = $component->get('departments');
        
        $this->assertTrue($departments->contains('IT'));
    }
}
