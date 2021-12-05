<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUpdatePasswordTest extends TestCase
{
    use RefreshDatabase;


    public function test_admin_password_can_be_updated()
    {
        $this->actingAs($user = Admin::factory()->create());

        $response = $this->put('/admin/user/password', [
            'current_password' => 'Pass@123',
            'password' => 'Hello@2k21',
            'password_confirmation' => 'Hello@2k21',
        ]);

        $this->assertTrue(Hash::check('Hello@2k21', $user->fresh()->password));
    }


    public function test_admin_current_password_must_be_correct()
    {
        $this->actingAs($user = Admin::factory()->create());

        $response = $this->put('/admin/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'Hello@2k21',
            'password_confirmation' => 'Hello@2k21',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('Pass@123', $user->fresh()->password));
    }


    public function test_admin_new_passwords_must_match()
    {
        $this->actingAs($user = Admin::factory()->create());

        $response = $this->put('/admin/user/password', [
            'current_password' => 'Pass@123',
            'password' => 'Pass@2k22',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('Pass@123', $user->fresh()->password));
    }
}
