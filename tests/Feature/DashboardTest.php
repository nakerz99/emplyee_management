<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\Announcement;
use Livewire\Livewire;
use App\Livewire\Admin\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

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
    }

    /** @test */
    public function admin_can_view_dashboard_with_pagination()
    {
        // Create some test data
        $employees = User::factory(15)->create([
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Create time records for some employees
        foreach ($employees->take(8) as $employee) {
            TimeRecord::factory()->create([
                'user_id' => $employee->id,
                'date' => today(),
                'status' => 'active',
            ]);
        }

        // Create announcements
        Announcement::factory(12)->create([
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Dashboard::class);

        // Check that pagination is working for active time records
        $activeTimeRecords = $component->get('activeTimeRecords');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $activeTimeRecords);
        $this->assertEquals(5, $activeTimeRecords->perPage()); // Should show 5 per page

        // Check that pagination is working for department spending
        $departmentSpending = $component->get('departmentSpending');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $departmentSpending);
        $this->assertEquals(5, $departmentSpending->perPage()); // Should show 5 per page

        // Check that pagination is working for recent announcements
        $recentAnnouncements = $component->get('recentAnnouncements');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $recentAnnouncements);
        $this->assertEquals(5, $recentAnnouncements->perPage()); // Should show 5 per page
    }

    /** @test */
    public function dashboard_shows_correct_data_counts()
    {
        // Create test data
        $employees = User::factory(10)->create([
            'role' => 'employee',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Dashboard::class);

        // Check total employees count
        $this->assertEquals(10, $component->get('totalEmployees'));
        $this->assertEquals(10, $component->get('activeEmployees'));
    }
}
