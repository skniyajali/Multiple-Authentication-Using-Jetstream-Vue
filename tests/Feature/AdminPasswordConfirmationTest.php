<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Jetstream\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminPasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_confirm_password_screen_can_be_rendered()
    {
        $user = Admin::factory()->create();

        $response = $this->actingAs($user)->get('/admin/user/confirm-password');

        $response->assertStatus(200);
    }

    public function test_admin_password_can_be_confirmed()
    {
        $user = Admin::factory()->create();

        $response = $this->actingAs($user)->post('/admin/user/confirm-password', [
            'password' => 'Pass@123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_admin_password_is_not_confirmed_with_invalid_password()
    {
        $user = Admin::factory()->create();

        $response = $this->actingAs($user)->post('/admin/user/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
