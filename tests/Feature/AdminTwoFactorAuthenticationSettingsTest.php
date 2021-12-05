<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTwoFactorAuthenticationSettingsTest extends TestCase
{
    use RefreshDatabase;



    public function test_admin_two_factor_authentication_can_be_enabled()
    {
        $this->actingAs($user = Admin::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $response = $this->post('/admin/user/two-factor-authentication');

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertCount(8, $user->fresh()->recoveryCodes());
    }


    public function test_admin_recovery_codes_can_be_regenerated()
    {
        $this->actingAs($user = Admin::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->post('/admin/user/two-factor-authentication');
        $this->post('/admin/user/two-factor-recovery-codes');

        $user = $user->fresh();

        $this->post('/admin/user/two-factor-recovery-codes');

        $this->assertCount(8, $user->recoveryCodes());
        $this->assertCount(8, array_diff($user->recoveryCodes(), $user->fresh()->recoveryCodes()));
    }


    public function test_admin_two_factor_authentication_can_be_disabled()
    {
        $this->actingAs($user = Admin::factory()->create());

        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->post('/admin/user/two-factor-authentication');

        $this->assertNotNull($user->fresh()->two_factor_secret);

        $this->delete('/admin/user/two-factor-authentication');

        $this->assertNull($user->fresh()->two_factor_secret);
    }
}
