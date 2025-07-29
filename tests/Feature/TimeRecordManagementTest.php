<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TimeRecord;
use Livewire\Livewire;
use App\Livewire\Admin\TimeRecordManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TimeRecordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee1;
    protected $employee2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create employees
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
    }

    /** @test */
    public function admin_can_view_time_record_management_page()
    {
        $this->actingAs($this->admin)
            ->get('/admin/time-records')
            ->assertStatus(200)
            ->assertSee('Time Record Management');
    }

    /** @test */
    public function non_admin_cannot_access_time_record_management()
    {
        $this->actingAs($this->employee1)
            ->get('/admin/time-records')
            ->assertRedirect('/');
    }

    /** @test */
    public function time_record_management_component_loads_without_records()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->assertSee('No time records found');
    }

    /** @test */
    public function can_search_time_records_by_employee_name()
    {
        // Create time record
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(17, 0),
            'total_hours' => 8.0,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('search', $this->employee1->name)
            ->assertSee($this->employee1->name)
            ->assertDontSee($this->employee2->name);
    }

    /** @test */
    public function can_filter_time_records_by_employee()
    {
        // Create time records for both employees
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

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('selectedEmployee', $this->employee1->id)
            ->assertSee($this->employee1->name)
            ->assertDontSee($this->employee2->name);
    }

    /** @test */
    public function can_filter_time_records_by_department()
    {
        // Create time records for both employees
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

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('selectedDepartment', 'Engineering')
            ->assertSee($this->employee1->name)
            ->assertDontSee($this->employee2->name);
    }

    /** @test */
    public function can_filter_time_records_by_date_range()
    {
        // Create time records on different dates
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => now()->subDays(5),
            'total_hours' => 8.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'date' => now()->subDays(10),
            'total_hours' => 7.5,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('dateFrom', now()->subDays(7)->format('Y-m-d'))
            ->set('dateTo', now()->subDays(3)->format('Y-m-d'))
            ->assertSee($this->employee1->name)
            ->assertDontSee($this->employee2->name);
    }

    /** @test */
    public function summary_stats_are_calculated_correctly()
    {
        // Create time records
        TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'total_hours' => 8.0,
            'overtime_hours' => 2.0,
        ]);

        TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
            'total_hours' => 7.5,
            'overtime_hours' => 0.5,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class);

        $stats = $component->call('getSummaryStatsProperty');
        
        $this->assertEquals(2, $stats['total_records']);
        $this->assertEquals(15.5, $stats['total_hours']);
        $this->assertEquals(2.5, $stats['total_overtime']);
        $this->assertEquals(7.75, $stats['average_hours']);
    }

    /** @test */
    public function can_open_time_record_modal()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertSet('editing', false)
            ->assertSet('date', now()->format('Y-m-d'));
    }

    /** @test */
    public function can_create_new_time_record()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('openModal')
            ->set('user_id', $this->employee1->id)
            ->set('date', now()->format('Y-m-d'))
            ->set('clock_in', '09:00')
            ->set('clock_out', '17:00')
            ->set('total_hours', 8.0)
            ->set('overtime_hours', 0.0)
            ->set('status', 'active')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('Time record created successfully!');

        // Verify time record was created
        $this->assertDatabaseHas('time_records', [
            'user_id' => $this->employee1->id,
            'total_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function time_record_validation_works()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('openModal')
            ->set('user_id', '')
            ->set('date', '')
            ->set('clock_in', '')
            ->call('save')
            ->assertHasErrors([
                'user_id' => 'required',
                'date' => 'required',
                'clock_in' => 'required',
            ]);
    }

    /** @test */
    public function clock_out_time_must_be_after_clock_in()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('openModal')
            ->set('user_id', $this->employee1->id)
            ->set('date', now()->format('Y-m-d'))
            ->set('clock_in', '17:00')
            ->set('clock_out', '09:00')
            ->call('save')
            ->assertHasErrors(['clock_out' => 'after']);
    }

    /** @test */
    public function can_edit_existing_time_record()
    {
        $timeRecord = TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
            'total_hours' => 8.0,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('openModal', $timeRecord->id)
            ->assertSet('editing', true)
            ->assertSet('user_id', $this->employee1->id)
            ->set('total_hours', 9.0)
            ->call('save')
            ->assertSee('Time record updated successfully!');

        $this->assertDatabaseHas('time_records', [
            'id' => $timeRecord->id,
            'total_hours' => 9.0,
        ]);
    }

    /** @test */
    public function can_delete_time_record()
    {
        $timeRecord = TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->call('deleteTimeRecord', $timeRecord->id)
            ->assertSee('Time record deleted successfully!');

        $this->assertDatabaseMissing('time_records', [
            'id' => $timeRecord->id,
        ]);
    }

    /** @test */
    public function bulk_operations_work()
    {
        $timeRecord1 = TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
        ]);

        $timeRecord2 = TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('selectedRecords', [$timeRecord1->id, $timeRecord2->id])
            ->call('bulkDelete')
            ->assertSee('time records deleted successfully!');

        $this->assertDatabaseMissing('time_records', [
            'id' => $timeRecord1->id,
        ]);
        $this->assertDatabaseMissing('time_records', [
            'id' => $timeRecord2->id,
        ]);
    }

    /** @test */
    public function select_all_functionality_works()
    {
        $timeRecord1 = TimeRecord::factory()->create([
            'user_id' => $this->employee1->id,
        ]);

        $timeRecord2 = TimeRecord::factory()->create([
            'user_id' => $this->employee2->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->set('selectAll', true)
            ->assertSet('selectedRecords', [(string) $timeRecord1->id, (string) $timeRecord2->id]);
    }

    /** @test */
    public function employees_list_is_populated()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->assertSee($this->employee1->name)
            ->assertSee($this->employee2->name);
    }

    /** @test */
    public function departments_list_is_populated()
    {
        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->assertSee('Engineering')
            ->assertSee('Marketing');
    }

    /** @test */
    public function pagination_works_for_time_records()
    {
        // Create many time records
        for ($i = 0; $i < 20; $i++) {
            TimeRecord::factory()->create([
                'user_id' => $this->employee1->id,
                'date' => now()->subDays($i),
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(TimeRecordManagement::class)
            ->assertSee('Time Record Management');
    }
} 