<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_email_verification_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::emailVerification())) {
            return $this->markTestSkipped('Email verification not enabled.');
        }

        $admin = Admin::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($admin,'admin')->get('/admin/email/verify');

        $response->assertStatus(200);
    }

    public function test_admin_email_can_be_verified()
    {
        if (! Features::enabled(Features::emailVerification())) {
            return $this->markTestSkipped('Email verification not enabled.');
        }

        Event::fake();

        $admin = Admin::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'admin.verification.verify',
            now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1($admin->email)]
        );

        $response = $this->actingAs($admin,'admin')->get($verificationUrl);
        dd($response);
        Event::assertDispatched(Verified::class);

        $this->assertTrue($admin->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME.'?verified=1');
    }

    public function test_admin_email_can_not_verified_with_invalid_hash()
    {
        if (! Features::enabled(Features::emailVerification())) {
            return $this->markTestSkipped('Email verification not enabled.');
        }

        $admin = Admin::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'admin.verification.verify',
            now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($admin)->get($verificationUrl);

        $this->assertFalse($admin->fresh()->hasVerifiedEmail());
    }
}
