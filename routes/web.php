<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Employee\Dashboard as EmployeeDashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Login route (accessible to everyone)
Route::get('/', Login::class)->name('login');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin/employees', \App\Livewire\Admin\EmployeeManagement::class)->name('admin.employees');
        Route::get('/admin/departments', \App\Livewire\Admin\DepartmentManagement::class)->name('admin.departments');
        Route::get('/admin/time-records', \App\Livewire\Admin\TimeRecordManagement::class)->name('admin.time-records');
        Route::get('/admin/leave-requests', \App\Livewire\Admin\LeaveRequestManagement::class)->name('admin.leave-requests');
        Route::get('/admin/reports', \App\Livewire\Admin\Reports::class)->name('admin.reports');
    });

    // Employee routes
    Route::middleware('employee')->group(function () {
        Route::get('/employee/dashboard', EmployeeDashboard::class)->name('employee.dashboard');
        Route::get('/employee/profile', \App\Livewire\Employee\Profile::class)->name('employee.profile');
        Route::get('/employee/leave-requests', \App\Livewire\Employee\LeaveRequest::class)->name('employee.leave-requests');
        Route::get('/employee/payslips', \App\Livewire\Employee\Payslip::class)->name('employee.payslips');
    });

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect()->route('login');
    })->name('logout');
});
