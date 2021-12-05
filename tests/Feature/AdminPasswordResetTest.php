<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reset_password_link_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
        }

        $response = $this->get('/admin/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
        }

        Notification::fake();

        $user = Admin::factory()->create();

        $response = $this->post('/admin/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_admin_reset_password_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
        }

        Notification::fake();

        $user = Admin::factory()->create();

        $response = $this->post('/admin/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/admin/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_admin_password_can_be_reset_with_valid_token()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
        }

        Notification::fake();

        $user = Admin::factory()->create();

        $response = $this->post('/admin/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/admin/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'Pass@2k21',
                'password_confirmation' => 'Pass@2k21',
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
