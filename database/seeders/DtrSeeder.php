<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\BreakSession;
use App\Models\Payslip;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DtrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@dtr.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'position' => 'System Administrator',
            'department' => 'Admin',
            'hourly_rate' => 50.00,
            'timezone' => 'America/New_York',
            'status' => 'active',
            'birthday' => '1985-03-15',
            'phone' => '+1-555-0123',
            'address' => '123 Admin Street, New York, NY 10001',
        ]);

        // Create Employees for IT Department (10 employees)
        $itEmployees = [];

        $itEmployees[] = User::create([
            'name' => "IT Employee Nak",
            'email' => "test@company.com",
            'password' => Hash::make('password'),
            'role' => 'employee',
            'position' => 1,
            'department' => 'IT',
            'hourly_rate' => rand(25, 45),
            'timezone' => 'America/New_York',
            'status' => 'active',
            'birthday' => $this->getRandomBirthday(),
            'phone' => "+1-555-IT",
            'address' => "100 Tech Street, San Francisco, CA 94102",
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $itEmployees[] = User::create([
                'name' => "IT Employee {$i}",
                'email' => "it.employee{$i}@company.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'position' => $this->getITPosition($i),
                'department' => 'IT',
                'hourly_rate' => rand(25, 45),
                'timezone' => 'America/New_York',
                'status' => 'active',
                'birthday' => $this->getRandomBirthday(),
                'phone' => "+1-555-IT{$i}",
                'address' => "{$i}00 Tech Street, San Francisco, CA 94102",
            ]);
        }

        // Create Employees for Finance Department (10 employees)
        $financeEmployees = [];
        for ($i = 1; $i <= 10; $i++) {
            $financeEmployees[] = User::create([
                'name' => "Finance Employee {$i}",
                'email' => "finance.employee{$i}@company.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'position' => $this->getFinancePosition($i),
                'department' => 'Finance',
                'hourly_rate' => rand(22, 40),
                'timezone' => 'America/New_York',
                'status' => 'active',
                'birthday' => $this->getRandomBirthday(),
                'phone' => "+1-555-FI{$i}",
                'address' => "{$i}00 Finance Ave, New York, NY 10001",
            ]);
        }

        // Create Employees for Admin Department (10 employees)
        $adminEmployees = [];
        for ($i = 1; $i <= 10; $i++) {
            $adminEmployees[] = User::create([
                'name' => "Admin Employee {$i}",
                'email' => "admin.employee{$i}@company.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'position' => $this->getAdminPosition($i),
                'department' => 'Admin',
                'hourly_rate' => rand(20, 35),
                'timezone' => 'America/New_York',
                'status' => 'active',
                'birthday' => $this->getRandomBirthday(),
                'phone' => "+1-555-AD{$i}",
                'address' => "{$i}00 Admin Blvd, Washington, DC 20001",
            ]);
        }

        $allEmployees = array_merge($itEmployees, $financeEmployees, $adminEmployees);

        // Create 3 months of time records (current month + 2 previous months)
        $this->createTimeRecords($allEmployees);

        // Create payslips for the last 3 months
        $this->createPayslips($allEmployees);

        // Create leave requests
        $this->createLeaveRequests($allEmployees);

        // Create announcements
        $this->createAnnouncements($admin);

        // Create notifications
        $this->createNotifications($allEmployees);
    }

    private function getITPosition($index)
    {
        $positions = [
            'Senior Software Engineer',
            'Frontend Developer',
            'Backend Developer',
            'DevOps Engineer',
            'QA Engineer',
            'System Administrator',
            'Network Engineer',
            'Database Administrator',
            'Security Engineer',
            'Technical Lead'
        ];
        return $positions[$index - 1] ?? 'IT Specialist';
    }

    private function getFinancePosition($index)
    {
        $positions = [
            'Senior Financial Analyst',
            'Accountant',
            'Financial Controller',
            'Budget Analyst',
            'Tax Specialist',
            'Auditor',
            'Payroll Specialist',
            'Investment Analyst',
            'Credit Analyst',
            'Finance Manager'
        ];
        return $positions[$index - 1] ?? 'Finance Specialist';
    }

    private function getAdminPosition($index)
    {
        $positions = [
            'HR Manager',
            'Office Administrator',
            'Executive Assistant',
            'Receptionist',
            'Facilities Manager',
            'Procurement Specialist',
            'Legal Assistant',
            'Compliance Officer',
            'Training Coordinator',
            'Administrative Assistant'
        ];
        return $positions[$index - 1] ?? 'Admin Specialist';
    }

    private function getRandomBirthday()
    {
        $start = Carbon::create(1980, 1, 1);
        $end = Carbon::create(2000, 12, 31);
        return $start->addDays(rand(0, $start->diffInDays($end)))->format('Y-m-d');
    }

    private function createTimeRecords($employees)
    {
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($currentDate->dayOfWeek !== 0 && $currentDate->dayOfWeek !== 6) {
                    // 85% chance of working on weekdays
                    if (rand(1, 100) <= 85) {
                        $this->createTimeRecordForDay($employee, $currentDate);
                    }
                }

                $currentDate->addDay();
            }
        }
    }

    private function createTimeRecordForDay($employee, $date)
    {
        // Base clock in time (8 AM to 10 AM)
        $clockInHour = rand(8, 10);
        $clockInMinute = rand(0, 59);
        $clockIn = $date->copy()->setTime($clockInHour, $clockInMinute);

        // Work 6-10 hours
        $workHours = rand(6, 10);
        $clockOut = $clockIn->copy()->addHours($workHours);

        // Calculate overtime (anything over 8 hours)
        $overtimeHours = max(0, $workHours - 8);
        $regularHours = $workHours - $overtimeHours;

        $timeRecord = TimeRecord::create([
            'user_id' => $employee->id,
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_hours' => $workHours,
            'break_hours' => rand(0, 1), // 0-1 hour break
            'overtime_hours' => $overtimeHours,
            'status' => 'active',
            'notes' => rand(1, 10) === 1 ? 'Worked from home' : null, // 10% chance of note
        ]);

        // Create break sessions (30% chance of having breaks)
        if (rand(1, 100) <= 30) {
            $this->createBreakSessions($timeRecord);
        }
    }

    private function createBreakSessions($timeRecord)
    {
        $numBreaks = rand(1, 3);
        $clockIn = Carbon::parse($timeRecord->clock_in);
        $clockOut = Carbon::parse($timeRecord->clock_out);
        $workDuration = $clockIn->diffInMinutes($clockOut);

        for ($i = 0; $i < $numBreaks; $i++) {
            $breakStart = $clockIn->copy()->addMinutes(rand(60, $workDuration - 60));
            $breakDuration = rand(15, 45); // 15-45 minute breaks
            $breakEnd = $breakStart->copy()->addMinutes($breakDuration);

            // Ensure break doesn't exceed work time
            if ($breakEnd <= $clockOut) {
                BreakSession::create([
                    'time_record_id' => $timeRecord->id,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'total_break_time' => $breakDuration,
                    'status' => 'completed',
                    'notes' => rand(1, 5) === 1 ? 'Lunch break' : null,
                ]);
            }
        }
    }

    private function createPayslips($employees)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 3; $i++) {
            $month = $currentMonth->copy()->subMonths($i);

            foreach ($employees as $employee) {
                // Get time records for this month
                $timeRecords = TimeRecord::where('user_id', $employee->id)
                    ->whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->get();

                if ($timeRecords->count() > 0) {
                    $totalHours = $timeRecords->sum('total_hours');
                    $overtimeHours = $timeRecords->sum('overtime_hours');
                    $regularHours = $totalHours - $overtimeHours;

                    $regularPay = $regularHours * $employee->hourly_rate;
                    $overtimePay = $overtimeHours * ($employee->hourly_rate * 1.5);
                    $totalPay = $regularPay + $overtimePay;

                    // Random deductions (0-10% of total pay)
                    $deductions = $totalPay * (rand(0, 10) / 100);
                    $netPay = $totalPay - $deductions;

                    Payslip::create([
                        'user_id' => $employee->id,
                        'month' => $month->month,
                        'year' => $month->year,
                        'total_hours' => $totalHours,
                        'regular_hours' => $regularHours,
                        'overtime_hours' => $overtimeHours,
                        'hourly_rate' => $employee->hourly_rate,
                        'regular_pay' => $regularPay,
                        'overtime_pay' => $overtimePay,
                        'total_pay' => $totalPay,
                        'deductions' => $deductions,
                        'net_pay' => $netPay,
                        'status' => 'generated',
                        'generated_at' => $month->endOfMonth(),
                        'paid_at' => rand(1, 10) === 1 ? null : $month->endOfMonth()->addDays(rand(1, 5)),
                    ]);
                }
            }
        }
    }

    private function createLeaveRequests($employees)
    {
        $leaveTypes = ['vacation', 'sick', 'personal', 'other'];
        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($employees as $employee) {
            // 40% chance of having leave requests
            if (rand(1, 100) <= 40) {
                $numRequests = rand(1, 3);

                for ($i = 0; $i < $numRequests; $i++) {
                    $startDate = Carbon::now()->subMonths(rand(1, 3))->addDays(rand(1, 20));
                    $daysRequested = rand(1, 5);
                    $endDate = $startDate->copy()->addDays($daysRequested - 1);

                    LeaveRequest::create([
                        'user_id' => $employee->id,
                        'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'days_requested' => $daysRequested,
                        'reason' => 'Personal leave request',
                        'status' => $statuses[array_rand($statuses)],
                        'approved_by' => rand(1, 10) === 1 ? null : 1, // 10% chance of not approved yet
                        'approved_at' => rand(1, 10) === 1 ? null : $startDate->subDays(rand(1, 7)),
                        'admin_notes' => rand(1, 5) === 1 ? 'Approved as requested' : null,
                    ]);
                }
            }
        }
    }

    private function createAnnouncements($admin)
    {
        $announcements = [
            [
                'title' => 'Company Holiday Schedule - December 2024',
                'content' => 'Please note that the office will be closed on December 25th and January 1st for the holidays. Happy holidays to everyone!',
                'priority' => 'normal',
                'is_active' => true,
                'scheduled_at' => Carbon::now()->subDays(30),
            ],
            [
                'title' => 'New Employee Benefits Package',
                'content' => 'We are excited to announce enhanced employee benefits including improved health insurance and additional PTO days.',
                'priority' => 'urgent',
                'is_active' => true,
                'scheduled_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Monthly Team Meeting - All Departments',
                'content' => 'Join us for our monthly all-hands meeting this Friday at 2 PM EST. We will discuss Q4 goals and upcoming projects.',
                'priority' => 'normal',
                'is_active' => true,
                'scheduled_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'System Maintenance Notice',
                'content' => 'The DTR system will undergo maintenance on Sunday from 2 AM to 6 AM EST. Please plan accordingly.',
                'priority' => 'urgent',
                'is_active' => true,
                'scheduled_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Welcome New Team Members',
                'content' => 'Please welcome our new team members across IT, Finance, and Admin departments. They will be joining us next week.',
                'priority' => 'normal',
                'is_active' => true,
                'scheduled_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::create(array_merge($announcementData, [
                'author_id' => $admin->id,
            ]));
        }
    }

    private function createNotifications($employees)
    {
        $notificationTypes = ['time_record', 'payslip', 'announcement', 'leave_request'];

        foreach ($employees as $employee) {
            $numNotifications = rand(5, 15);

            for ($i = 0; $i < $numNotifications; $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $isRead = rand(1, 100) <= 70; // 70% chance of being read

                Notification::create([
                    'user_id' => $employee->id,
                    'type' => $type,
                    'title' => $this->getNotificationTitle($type),
                    'message' => $this->getNotificationMessage($type),
                    'data' => json_encode(['timestamp' => Carbon::now()->subDays(rand(1, 30))]),
                    'is_read' => $isRead,
                    'read_at' => $isRead ? Carbon::now()->subDays(rand(1, 30)) : null,
                ]);
            }
        }
    }

    private function getNotificationTitle($type)
    {
        $titles = [
            'time_record' => 'Time Record Updated',
            'payslip' => 'Payslip Generated',
            'announcement' => 'New Announcement',
            'leave_request' => 'Leave Request Update',
        ];

        return $titles[$type] ?? 'System Notification';
    }

    private function getNotificationMessage($type)
    {
        $messages = [
            'time_record' => 'Your time record has been updated for today.',
            'payslip' => 'Your monthly payslip has been generated and is ready for review.',
            'announcement' => 'A new company announcement has been posted.',
            'leave_request' => 'Your leave request status has been updated.',
        ];

        return $messages[$type] ?? 'You have a new notification.';
    }
}
