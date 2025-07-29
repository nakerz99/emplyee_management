<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Employee Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('employee.profile') }}" class="text-sm text-blue-600 hover:text-blue-800">My Profile</a>
                    <span class="text-sm text-gray-500">Welcome, {{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Time Tracking Section -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="text-center">
                    <div class="text-4xl font-bold text-gray-900 mb-4" id="currentTime">
                        {{ now()->format('g:i:s A') }}
                    </div>
                    <p class="text-sm text-gray-500 mb-6" id="currentDate">{{ now()->format('l, F j, Y') }}</p>
                    
                    <div class="flex justify-center space-x-4">
                        @if(!$isClockedIn)
                            <button wire:click="clockIn" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                                Clock In
                            </button>
                        @else
                            <button wire:click="clockOut" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg">
                                Clock Out
                            </button>
                            
                            @if(!$isOnBreak)
                                <button wire:click="startBreak" 
                                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg">
                                    Start Break
                                </button>
                            @else
                                <button wire:click="endBreak" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                                    End Break
                                </button>
                            @endif
                        @endif
                    </div>

                    @if($isClockedIn)
                        <div class="mt-4 p-4 bg-green-50 rounded-lg">
                            <p class="text-sm text-green-800">
                                <strong>Status:</strong> 
                                @if($isOnBreak)
                                    On Break
                                @else
                                    Working
                                @endif
                            </p>
                            @if($todayRecord && $todayRecord->clock_in)
                                <p class="text-sm text-green-800">
                                    <strong>Clocked in:</strong> {{ $todayRecord->formatted_clock_in }}
                                </p>
                            @endif
                            
                            <!-- Simple note input for clock out -->
                            <div class="mt-3">
                                <label for="clockOutNote" class="block text-sm font-medium text-gray-700 mb-1">
                                    Work Summary (Optional)
                                </label>
                                <textarea 
                                    wire:model="clockOutNote"
                                    id="clockOutNote"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="What did you work on today? (e.g., Completed project tasks, attended meetings, etc.)"
                                ></textarea>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Hours Today</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalHoursToday, 2) }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Hours This Month</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($totalHoursThisMonth, 2) }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Leave Requests</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $myLeaveRequests->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Announcements -->
        @if($recentAnnouncements->count() > 0)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Announcements</h3>
                <div class="space-y-4">
                    @foreach($recentAnnouncements as $announcement)
                    <div class="border-l-4 border-blue-400 pl-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">{{ $announcement->title }}</h4>
                            <span class="text-xs text-gray-500">{{ $announcement->created_at->format('M j, Y') }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($announcement->content, 150) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Leave Requests -->
        @if($myLeaveRequests->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">My Leave Requests</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($myLeaveRequests as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ ucfirst($request->leave_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($request->status === 'approved') bg-green-100 text-green-800
                                        @elseif($request->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->days_requested }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// Update time display every second without Livewire polling
function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('currentTime');
    const dateElement = document.getElementById('currentDate');
    
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        });
    }
    
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
}

// Update time immediately and then every second
updateTime();
setInterval(updateTime, 1000);
</script>
