<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\BreakSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class TimeRecordTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_time_record_can_be_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('time_records', [
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'active',
        ]);

        $this->assertEquals($user->id, $timeRecord->user_id);
        $this->assertEquals('active', $timeRecord->status);
    }

    public function test_time_record_can_calculate_total_hours()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $clockIn = now()->subHours(8);
        $clockOut = now();

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'completed',
        ]);

        $totalHours = $timeRecord->calculateTotalHours();
        $this->assertEquals(8.0, $totalHours);
    }

    public function test_time_record_can_calculate_overtime_hours()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $clockIn = now()->subHours(10);
        $clockOut = now();

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'completed',
        ]);

        $overtimeHours = $timeRecord->calculateOvertimeHours();
        $this->assertEquals(2.0, $overtimeHours);
    }

    public function test_time_record_can_start_break()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(4),
            'status' => 'active',
        ]);

        $breakSession = $timeRecord->startBreak();

        $this->assertDatabaseHas('break_sessions', [
            'time_record_id' => $timeRecord->id,
            'status' => 'active',
        ]);

        $this->assertTrue($timeRecord->isOnBreak());
        $this->assertEquals($breakSession->id, $timeRecord->getCurrentBreak()->id);
    }

    public function test_time_record_can_end_break()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(4),
            'status' => 'active',
        ]);

        $breakSession = $timeRecord->startBreak();
        
        // Wait a bit to simulate break time
        sleep(1);
        
        $result = $timeRecord->endBreak();

        $this->assertTrue($result);
        $this->assertFalse($timeRecord->isOnBreak());
        
        $breakSession->refresh();
        $this->assertEquals('completed', $breakSession->status);
        $this->assertNotNull($breakSession->break_end);
    }

    public function test_time_record_can_calculate_total_break_time()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'status' => 'active',
        ]);

        // Create completed break sessions
        BreakSession::create([
            'time_record_id' => $timeRecord->id,
            'break_start' => now()->subHours(6),
            'break_end' => now()->subHours(5.5),
            'total_break_time' => 0.5,
            'status' => 'completed',
        ]);

        BreakSession::create([
            'time_record_id' => $timeRecord->id,
            'break_start' => now()->subHours(3),
            'break_end' => now()->subHours(2.5),
            'total_break_time' => 0.5,
            'status' => 'completed',
        ]);

        $totalBreakTime = $timeRecord->getTotalBreakTime();
        $this->assertEquals(1.0, $totalBreakTime);
    }

    public function test_time_record_has_formatted_attributes()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $clockIn = Carbon::parse('2024-01-15 09:00:00');
        $clockOut = Carbon::parse('2024-01-15 17:00:00');

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_hours' => 8.0,
            'status' => 'completed',
        ]);

        $this->assertEquals('9:00 AM', $timeRecord->formatted_clock_in);
        $this->assertEquals('5:00 PM', $timeRecord->formatted_clock_out);
        $this->assertEquals('8:00', $timeRecord->duration);
    }

    public function test_time_record_relationships()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        // Test user relationship
        $this->assertEquals($user->id, $timeRecord->user->id);

        // Test break sessions relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $timeRecord->breakSessions);
    }

    public function test_user_can_clock_in_and_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        // Test initial state
        $this->assertFalse($user->isClockedIn());
        $this->assertNull($user->todayTimeRecord());

        // Clock in
        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'active',
        ]);

        $user->refresh();
        $this->assertTrue($user->isClockedIn());
        $this->assertNotNull($user->todayTimeRecord());

        // Clock out
        $timeRecord->update([
            'clock_out' => now()->addHours(8),
            'total_hours' => 8.0,
            'status' => 'completed',
        ]);

        $user->refresh();
        $this->assertFalse($user->isClockedIn());
    }

    public function test_user_can_take_breaks()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(4),
            'status' => 'active',
        ]);

        // Test initial state
        $this->assertFalse($user->isOnBreak());

        // Start break
        $timeRecord->startBreak();
        $user->refresh();
        $this->assertTrue($user->isOnBreak());

        // End break
        $timeRecord->endBreak();
        $user->refresh();
        $this->assertFalse($user->isOnBreak());
    }

    public function test_prevents_duplicate_time_records_for_same_user_same_date()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $date = today();

        // Create first time record
        $timeRecord1 = TimeRecord::getOrCreateForUserOnDate($user->id, $date, [
            'clock_in' => now()->subHours(8),
            'status' => 'active',
        ]);

        // Try to create another time record for the same user on the same date
        $timeRecord2 = TimeRecord::getOrCreateForUserOnDate($user->id, $date, [
            'clock_in' => now()->subHours(4),
            'status' => 'active',
        ]);

        // Should return the same record, not create a duplicate
        $this->assertEquals($timeRecord1->id, $timeRecord2->id);
        $this->assertEquals(1, TimeRecord::where('user_id', $user->id)->where('date', $date)->count());
    }

    public function test_time_record_helper_methods()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $date = today();

        // Test existsForUserOnDate
        $this->assertFalse(TimeRecord::existsForUserOnDate($user->id, $date));

        // Create a time record
        TimeRecord::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => now(),
            'status' => 'active',
        ]);

        // Test existsForUserOnDate again
        $this->assertTrue(TimeRecord::existsForUserOnDate($user->id, $date));
    }

    public function test_user_can_add_note_when_clocking_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        // Clock in
        $timeRecord = TimeRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'status' => 'active',
        ]);

        $this->assertTrue($user->isClockedIn());

        // Clock out with note
        $note = 'Completed project tasks and attended team meetings';
        $timeRecord->update([
            'clock_out' => now(),
            'total_hours' => 8,
            'status' => 'completed',
            'notes' => $note,
        ]);

        $this->assertFalse($user->fresh()->isClockedIn());
        $this->assertEquals($note, $timeRecord->fresh()->notes);
        $this->assertEquals('completed', $timeRecord->fresh()->status);
    }
}
