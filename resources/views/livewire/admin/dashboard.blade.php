<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <nav class="flex space-x-4">
                        <a href="{{ route('admin.employees') }}" class="text-sm text-blue-600 hover:text-blue-800">Employees</a>
                        <a href="{{ route('admin.departments') }}" class="text-sm text-blue-600 hover:text-blue-800">Departments</a>
                        <a href="{{ route('admin.time-records') }}" class="text-sm text-blue-600 hover:text-blue-800">Time Records</a>
                        <a href="{{ route('admin.leave-requests') }}" class="text-sm text-blue-600 hover:text-blue-800">Leave Requests</a>
                        <a href="{{ route('admin.reports') }}" class="text-sm text-blue-600 hover:text-blue-800">Reports</a>
                    </nav>
                    <span class="text-sm text-gray-500">Welcome, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $totalEmployees }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Employees</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $activeEmployees }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">On Leave</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $employeesOnLeave }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Monthly Spending</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($monthlySpending, 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Currently Working -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Currently Working</h3>
                    <div class="space-y-3">
                        @forelse($this->activeTimeRecords as $record)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-md">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $record->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $record->user->department }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900">{{ $record->formatted_clock_in }}</p>
                                    <p class="text-xs text-gray-500">Clocked in</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No employees currently working</p>
                        @endforelse
                    </div>
                    
                    <!-- Pagination for Currently Working -->
                    @if($this->activeTimeRecords->hasPages())
                        <div class="mt-4">
                            {{ $this->activeTimeRecords->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Department Spending -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Department Spending</h3>
                    <div class="space-y-3">
                        @forelse($this->departmentSpending as $dept)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-md">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $dept->department }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">${{ number_format($dept->total_spending, 2) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No department data available</p>
                        @endforelse
                    </div>
                    
                    <!-- Pagination for Department Spending -->
                    @if($this->departmentSpending->hasPages())
                        <div class="mt-4">
                            {{ $this->departmentSpending->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Birthday Celebrants -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Birthday Celebrants</h3>
                    <div class="space-y-3">
                        @forelse($birthdayCelebrants as $user)
                            <div class="flex items-center p-3 bg-pink-50 rounded-md">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-pink-500 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->birthday->format('M j') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No birthdays this month</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Announcements -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Announcements</h3>
                    <div class="space-y-3">
                        @forelse($this->recentAnnouncements as $announcement)
                            <div class="p-3 bg-yellow-50 rounded-md">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $announcement->title }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $announcement->priority_badge_class }}">
                                        {{ $announcement->priority_name }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600">{{ Str::limit($announcement->content, 100) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent announcements</p>
                        @endforelse
                    </div>
                    
                    <!-- Pagination for Recent Announcements -->
                    @if($this->recentAnnouncements->hasPages())
                        <div class="mt-4">
                            {{ $this->recentAnnouncements->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Leave Requests -->
            <div class="bg-white shadow rounded-lg lg:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Pending Leave Requests</h3>
                        <a href="{{ route('admin.leave-requests') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pendingLeaveRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $request->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->user->department }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $request->leave_type_name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $request->formatted_date_range }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $request->days_requested }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->status_badge_class }}">
                                                {{ $request->status_name }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No pending leave requests</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
