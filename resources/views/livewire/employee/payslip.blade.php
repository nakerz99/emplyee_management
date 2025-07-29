<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-semibold text-gray-900">My Payslips</h1>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Payslips</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $this->payslipStats['total_payslips'] }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($this->payslipStats['total_earnings'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Deductions</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($this->payslipStats['total_deductions'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Net Pay</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($this->payslipStats['total_net_pay'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Payslips</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                        <select wire:model="selectedMonth" id="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Months</option>
                            @foreach($this->months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                        <select wire:model="selectedYear" id="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Years</option>
                            @foreach($this->years as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payslips Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Payslip History</h3>
            </div>
            <div class="border-t border-gray-200">
                @if($this->payslips->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($this->payslips as $payslip)
                            <li class="px-4 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $this->months[$payslip->month] }} {{ $payslip->year }}
                                                </p>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($payslip->status === 'paid') bg-green-100 text-green-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    {{ ucfirst($payslip->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500">
                                                Total Hours: {{ $payslip->total_hours }} | Regular: {{ $payslip->regular_hours }} | Overtime: {{ $payslip->overtime_hours }}
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500">
                                                Hourly Rate: ${{ number_format($payslip->hourly_rate, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">${{ number_format($payslip->net_pay, 2) }}</p>
                                            <p class="text-sm text-gray-500">Net Pay</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button wire:click="viewPayslip({{ $payslip->id }})" 
                                                    class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                View
                                            </button>
                                            <button wire:click="downloadPayslip({{ $payslip->id }})" 
                                                    class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                Download
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $this->payslips->links() }}
                    </div>
                @else
                    <div class="px-4 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payslips found</h3>
                        <p class="mt-1 text-sm text-gray-500">No payslips match your current filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payslip Detail Modal -->
    @if($showPayslipModal && $selectedPayslip)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Payslip - {{ $this->months[$selectedPayslip->month] }} {{ $selectedPayslip->year }}
                                </h3>
                                <div class="mt-4">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700">Employee Information</h4>
                                                <p class="text-sm text-gray-900">{{ auth()->user()->name }}</p>
                                                <p class="text-sm text-gray-500">{{ auth()->user()->position }}</p>
                                                <p class="text-sm text-gray-500">{{ auth()->user()->department }}</p>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700">Pay Period</h4>
                                                <p class="text-sm text-gray-900">{{ $this->months[$selectedPayslip->month] }} {{ $selectedPayslip->year }}</p>
                                                <p class="text-sm text-gray-500">Status: {{ ucfirst($selectedPayslip->status) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-4">
                                        <div class="bg-white border rounded-lg p-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Hours Worked</h4>
                                            <div class="grid grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <p class="text-gray-500">Total Hours</p>
                                                    <p class="font-medium">{{ $selectedPayslip->total_hours }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Regular Hours</p>
                                                    <p class="font-medium">{{ $selectedPayslip->regular_hours }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Overtime Hours</p>
                                                    <p class="font-medium">{{ $selectedPayslip->overtime_hours }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white border rounded-lg p-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Pay Breakdown</h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">Regular Pay ({{ $selectedPayslip->regular_hours }} hrs × ${{ number_format($selectedPayslip->hourly_rate, 2) }})</span>
                                                    <span class="font-medium">${{ number_format($selectedPayslip->regular_pay, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">Overtime Pay ({{ $selectedPayslip->overtime_hours }} hrs × ${{ number_format($selectedPayslip->hourly_rate * 1.5, 2) }})</span>
                                                    <span class="font-medium">${{ number_format($selectedPayslip->overtime_pay, 2) }}</span>
                                                </div>
                                                <div class="border-t pt-2 flex justify-between">
                                                    <span class="text-gray-700 font-medium">Total Pay</span>
                                                    <span class="font-medium">${{ number_format($selectedPayslip->total_pay, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between text-red-600">
                                                    <span class="text-gray-500">Deductions</span>
                                                    <span class="font-medium">-${{ number_format($selectedPayslip->deductions, 2) }}</span>
                                                </div>
                                                <div class="border-t pt-2 flex justify-between">
                                                    <span class="text-gray-900 font-semibold">Net Pay</span>
                                                    <span class="font-semibold text-lg">${{ number_format($selectedPayslip->net_pay, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="downloadPayslip({{ $selectedPayslip->id }})" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Download PDF
                        </button>
                        <button type="button" wire:click="closePayslipModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 