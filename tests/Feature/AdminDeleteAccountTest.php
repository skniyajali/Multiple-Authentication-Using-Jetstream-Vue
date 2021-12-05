<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Laravel\Jetstream\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminDeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_accounts_can_be_deleted()
    {
        if (! Features::hasAccountDeletionFeatures()) {
            return $this->markTestSkipped('Account deletion is not enabled.');
        }

        $user = Admin::factory()->create();

        $this->actingAs($user);

        $response = $this->delete('/admin/user', [
            'password' => 'Pass@123',
        ]);



        $this->assertNull($user->fresh());
    }

    public function test_admin_correct_password_must_be_provided_before_account_can_be_deleted()
    {
        if (! Features::hasAccountDeletionFeatures()) {
            return $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = Admin::factory()->create());

        $response = $this->delete('/admin/user', [
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh());
    }
}
