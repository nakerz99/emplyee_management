<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_guest_can_access_login_page()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('DTR System');
        $response->assertSee('Sign in to your account');
    }

    public function test_admin_can_login_successfully()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/livewire/update', [
            'fingerprint' => 'test',
            'serverMemo' => [
                'children' => [],
                'errors' => [],
                'htmlHash' => 'test',
                'data' => [
                    'email' => 'admin@example.com',
                    'password' => 'password',
                    'remember' => false,
                ],
                'dataMeta' => [],
                'checksum' => 'test',
            ],
            'updates' => [
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'id' => 'test',
                        'method' => 'login',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_employee_can_login_successfully()
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->post('/livewire/update', [
            'fingerprint' => 'test',
            'serverMemo' => [
                'children' => [],
                'errors' => [],
                'htmlHash' => 'test',
                'data' => [
                    'email' => 'employee@example.com',
                    'password' => 'password',
                    'remember' => false,
                ],
                'dataMeta' => [],
                'checksum' => 'test',
            ],
            'updates' => [
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'id' => 'test',
                        'method' => 'login',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isEmployee());
    }

    public function test_invalid_credentials_show_error()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->post('/livewire/update', [
            'fingerprint' => 'test',
            'serverMemo' => [
                'children' => [],
                'errors' => [],
                'htmlHash' => 'test',
                'data' => [
                    'email' => 'test@example.com',
                    'password' => 'wrongpassword',
                    'remember' => false,
                ],
                'dataMeta' => [],
                'checksum' => 'test',
            ],
            'updates' => [
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'id' => 'test',
                        'method' => 'login',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $this->assertGuest();
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    public function test_employee_cannot_access_admin_dashboard()
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get('/admin/dashboard');
        
        $response->assertRedirect('/');
    }

    public function test_employee_can_access_employee_dashboard()
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get('/employee/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Employee Dashboard');
    }

    public function test_admin_cannot_access_employee_dashboard()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/employee/dashboard');
        
        $response->assertRedirect('/');
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/');

        $response = $this->get('/employee/dashboard');
        $response->assertRedirect('/');
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_validation_requires_email()
    {
        $response = $this->post('/livewire/update', [
            'fingerprint' => 'test',
            'serverMemo' => [
                'children' => [],
                'errors' => [],
                'htmlHash' => 'test',
                'data' => [
                    'email' => '',
                    'password' => 'password',
                    'remember' => false,
                ],
                'dataMeta' => [],
                'checksum' => 'test',
            ],
            'updates' => [
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'id' => 'test',
                        'method' => 'login',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $this->assertGuest();
    }

    public function test_login_validation_requires_password()
    {
        $response = $this->post('/livewire/update', [
            'fingerprint' => 'test',
            'serverMemo' => [
                'children' => [],
                'errors' => [],
                'htmlHash' => 'test',
                'data' => [
                    'email' => 'test@example.com',
                    'password' => '',
                    'remember' => false,
                ],
                'dataMeta' => [],
                'checksum' => 'test',
            ],
            'updates' => [
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'id' => 'test',
                        'method' => 'login',
                        'params' => [],
                    ],
                ],
            ],
        ]);

        $this->assertGuest();
    }
}
