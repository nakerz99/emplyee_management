<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Department Management</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                    <button wire:click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Add Department
                    </button>
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

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input wire:model.live="search" type="text" placeholder="Search departments..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model.live="selectedStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button wire:click="$set('search', '')" wire:click="$set('selectedStatus', '')" 
                                class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->departments as $department)
                @php
                    $stats = $this->getDepartmentStats($department->department);
                @endphp
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $department->department }}</h3>
                                <p class="text-sm text-gray-500">Department</p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="openModal({{ $loop->index }})" class="text-blue-600 hover:text-blue-900 text-sm">
                                    Edit
                                </button>
                                <button wire:click="deleteDepartment({{ $loop->index }})" 
                                        onclick="return confirm('Are you sure you want to delete this department?')"
                                        class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                            </div>
                        </div>

                        <!-- Department Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_employees'] }}</div>
                                <div class="text-xs text-gray-500">Total Employees</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $stats['active_employees'] }}</div>
                                <div class="text-xs text-gray-500">Active</div>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <div class="text-center">
                                <div class="text-lg font-bold text-purple-600">${{ number_format($stats['monthly_spending'], 2) }}</div>
                                <div class="text-xs text-gray-500">Monthly Spending</div>
                            </div>
                        </div>

                        <!-- Department Employees -->
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Employees</h4>
                            <div class="space-y-1">
                                @foreach(\App\Models\User::where('department', $department->department)->where('role', 'employee')->take(3)->get() as $employee)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">{{ $employee->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 
                                               ($employee->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </div>
                                @endforeach
                                @if(\App\Models\User::where('department', $department->department)->where('role', 'employee')->count() > 3)
                                    <div class="text-xs text-gray-500 text-center">
                                        +{{ \App\Models\User::where('department', $department->department)->where('role', 'employee')->count() - 3 }} more
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6 text-center">
                            <p class="text-gray-500">No departments found</p>
                            <button wire:click="openModal()" class="mt-2 text-blue-600 hover:text-blue-800">
                                Create your first department
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->departments->links() }}
        </div>
    </div>

    <!-- Department Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editing ? 'Edit Department' : 'Add New Department' }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department Name *</label>
                                <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department Manager</label>
                                <select wire:model="manager_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Manager</option>
                                    @foreach($this->managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->position }})</option>
                                    @endforeach
                                </select>
                                @error('manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Budget</label>
                                <input wire:model="budget" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                <select wire:model="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" wire:click="closeModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                {{ $editing ? 'Update' : 'Create' }} Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
