<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = Admin::factory()->create();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'Pass@123',
        ]);

        $this->assertAuthenticatedAs(Admin::class,'guest:admin');

        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }
}
