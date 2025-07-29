<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_be_created_with_dtr_fields()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'position' => 'Developer',
            'department' => 'Engineering',
            'hourly_rate' => 25.00,
            'timezone' => 'America/New_York',
            'status' => 'active',
            'birthday' => '1990-01-15',
            'phone' => '+1-555-0123',
            'address' => '123 Main St, New York, NY',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'employee',
            'position' => 'Developer',
            'department' => 'Engineering',
            'hourly_rate' => 25.00,
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('employee', $user->role);
    }

    public function test_user_is_admin_method()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($employee->isAdmin());
    }

    public function test_user_is_employee_method()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertTrue($employee->isEmployee());
        $this->assertFalse($admin->isEmployee());
    }

    public function test_user_can_get_today_time_record()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertNull($user->todayTimeRecord());

        // Create a time record for today
        $timeRecord = $user->timeRecords()->create([
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        $this->assertEquals($timeRecord->id, $user->todayTimeRecord()->id);
    }

    public function test_user_is_clocked_in_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertFalse($user->isClockedIn());

        // Create an active time record
        $user->timeRecords()->create([
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        $this->assertTrue($user->isClockedIn());
    }

    public function test_user_is_on_break_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertFalse($user->isOnBreak());

        // Create a time record with active break
        $timeRecord = $user->timeRecords()->create([
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        $timeRecord->breakSessions()->create([
            'break_start' => now(),
            'status' => 'active',
        ]);

        $this->assertTrue($user->isOnBreak());
    }

    public function test_user_can_get_birthday_celebrants()
    {
        $currentMonth = now()->month;

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'birthday' => now()->setMonth($currentMonth)->setDay(15),
            'status' => 'active',
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'birthday' => now()->setMonth($currentMonth)->setDay(20),
            'status' => 'active',
        ]);

        $user3 = User::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'birthday' => now()->setMonth($currentMonth + 1)->setDay(15), // Next month
            'status' => 'active',
        ]);

        $celebrants = User::getBirthdayCelebrants();

        $this->assertCount(2, $celebrants);
        $this->assertTrue($celebrants->contains($user1));
        $this->assertTrue($celebrants->contains($user2));
        $this->assertFalse($celebrants->contains($user3));
    }

    public function test_user_can_get_total_hours_for_month()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Create time records for current month
        $user->timeRecords()->createMany([
            [
                'date' => now()->setDay(1),
                'total_hours' => 8.0,
                'status' => 'completed',
            ],
            [
                'date' => now()->setDay(2),
                'total_hours' => 7.5,
                'status' => 'completed',
            ],
        ]);

        $totalHours = $user->getTotalHoursForMonth($currentMonth, $currentYear);
        $this->assertEquals(15.5, $totalHours);
    }

    public function test_user_can_get_total_pay_for_month()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'hourly_rate' => 20.00,
        ]);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Create time records for current month
        $user->timeRecords()->createMany([
            [
                'date' => now()->setDay(1),
                'total_hours' => 8.0,
                'status' => 'completed',
            ],
            [
                'date' => now()->setDay(2),
                'total_hours' => 7.5,
                'status' => 'completed',
            ],
        ]);

        $totalPay = $user->getTotalPayForMonth($currentMonth, $currentYear);
        $this->assertEquals(310.00, $totalPay); // (8.0 + 7.5) * 20.00
    }

    public function test_user_can_get_current_timezone()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'timezone' => 'Asia/Manila',
        ]);

        $this->assertEquals('Asia/Manila', $user->getCurrentTimezone());

        // Test default timezone
        $userWithoutTimezone = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $this->assertEquals('America/New_York', $userWithoutTimezone->getCurrentTimezone());
    }

    public function test_user_relationships()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        // Test timeRecords relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->timeRecords);

        // Test payslips relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->payslips);

        // Test leaveRequests relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->leaveRequests);

        // Test notifications relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->notifications);
    }
}
