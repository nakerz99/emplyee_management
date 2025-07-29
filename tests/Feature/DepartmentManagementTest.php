<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TimeRecord;
use Livewire\Livewire;
use App\Livewire\Admin\DepartmentManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentManagementTest extends TestCase
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

        // Create employees with departments
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
    public function admin_can_view_department_management_page()
    {
        $this->actingAs($this->admin)
            ->get('/admin/departments')
            ->assertStatus(200)
            ->assertSee('Department Management');
    }

    /** @test */
    public function non_admin_cannot_access_department_management()
    {
        $this->actingAs($this->employee1)
            ->get('/admin/departments')
            ->assertRedirect('/');
    }

    /** @test */
    public function department_management_component_loads_departments()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->assertSee('Engineering')
            ->assertSee('Marketing');
    }

    /** @test */
    public function can_search_departments()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->set('search', 'Engineering')
            ->assertSee('Engineering')
            ->assertDontSee('Marketing');
    }

    /** @test */
    public function can_filter_departments_by_status()
    {
        // Create inactive employee
        User::factory()->create([
            'role' => 'employee',
            'department' => 'HR',
            'status' => 'inactive',
        ]);

        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->set('selectedStatus', 'active')
            ->assertSee('Engineering')
            ->assertSee('Marketing')
            ->assertDontSee('HR');
    }

    /** @test */
    public function department_stats_are_calculated_correctly()
    {
        // Create time records for employees
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
            ->test(DepartmentManagement::class);

        // Check Engineering department stats
        $engineeringStats = $component->call('getDepartmentStats', 'Engineering');
        $this->assertEquals(1, $engineeringStats['total_employees']);
        $this->assertEquals(1, $engineeringStats['active_employees']);
        $this->assertEquals(200.00, $engineeringStats['monthly_spending']); // 8 hours * $25

        // Check Marketing department stats
        $marketingStats = $component->call('getDepartmentStats', 'Marketing');
        $this->assertEquals(1, $marketingStats['total_employees']);
        $this->assertEquals(1, $marketingStats['active_employees']);
        $this->assertEquals(240.00, $marketingStats['monthly_spending']); // 8 hours * $30
    }

    /** @test */
    public function can_open_department_modal()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertSet('editing', false);
    }

    /** @test */
    public function can_create_new_department()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->call('openModal')
            ->set('name', 'Sales')
            ->set('description', 'Sales department')
            ->set('budget', 50000)
            ->set('status', 'active')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('Department created successfully!');

        // Verify department was created
        $this->assertDatabaseHas('users', [
            'department' => 'Sales',
            'role' => 'employee',
            'status' => 'inactive', // Department placeholder
        ]);
    }

    /** @test */
    public function department_name_is_required()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->call('openModal')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function department_name_must_be_unique()
    {
        // Create existing department
        User::factory()->create([
            'role' => 'employee',
            'department' => 'Sales',
        ]);

        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->call('openModal')
            ->set('name', 'Sales')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    /** @test */
    public function can_delete_department()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->call('deleteDepartment', 1)
            ->assertSee('Department deleted successfully!');
    }

    /** @test */
    public function managers_list_is_populated()
    {
        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->assertSee($this->employee1->name)
            ->assertSee($this->employee2->name);
    }

    /** @test */
    public function pagination_works_for_departments()
    {
        // Create many departments
        for ($i = 0; $i < 15; $i++) {
            User::factory()->create([
                'role' => 'employee',
                'department' => "Department {$i}",
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(DepartmentManagement::class)
            ->assertSee('Department 0')
            ->assertSee('Department 9')
            ->assertDontSee('Department 14'); // Should be on next page
    }
} 