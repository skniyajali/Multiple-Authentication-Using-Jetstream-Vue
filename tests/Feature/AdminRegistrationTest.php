<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registration_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::registration())) {
            return $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/admin/register');

        $response->assertStatus(200);
    }

    public function test_admin_registration_screen_cannot_be_rendered_if_support_is_disabled()
    {
        if (Features::enabled(Features::registration())) {
            return $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/admin/register');

        $response->assertStatus(404);
    }

    public function test_new_admin_users_can_register()
    {
        if (! Features::enabled(Features::registration())) {
            return $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/admin/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass@2k21',
            'password_confirmation' => 'Pass@2k21',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }
}
