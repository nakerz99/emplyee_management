<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('employee.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
                    
                    <form wire:submit="updateProfile">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input wire:model="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input wire:model="phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Timezone *</label>
                                <select wire:model="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="America/New_York">Eastern Time (EST/EDT)</option>
                                    <option value="Asia/Manila">Philippines (PHT)</option>
                                    <option value="Asia/Kolkata">India (IST)</option>
                                    <option value="Europe/London">London (GMT/BST)</option>
                                    <option value="Asia/Tokyo">Japan (JST)</option>
                                    <option value="Australia/Sydney">Australia (AEST/AEDT)</option>
                                </select>
                                @error('timezone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea wire:model="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    
                    <form wire:submit="updatePassword">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                                <input wire:model="current_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                                <input wire:model="new_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('new_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                                <input wire:model="confirm_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('confirm_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Employee Information (Read-only) -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->id }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->position ?? 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->department ?? 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hourly Rate</label>
                        <p class="mt-1 text-sm text-gray-900">${{ number_format($user->hourly_rate ?? 0, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($user->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst(str_replace('_', ' ', $user->status ?? 'active')) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Birthday</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->birthday ? $user->birthday->format('F j, Y') : 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Member Since</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F j, Y') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
