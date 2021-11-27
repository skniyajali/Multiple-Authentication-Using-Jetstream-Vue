<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/password', [
            'current_password' => 'Pass@123',
            'password' => 'Hello@2k21',
            'password_confirmation' => 'Hello@2k21',
        ]);

        $this->assertTrue(Hash::check('Hello@2k21', $user->fresh()->password));
    }

    public function test_current_password_must_be_correct()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'Hello@2k21',
            'password_confirmation' => 'Hello@2k21',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('Pass@123', $user->fresh()->password));
    }

    public function test_new_passwords_must_match()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/password', [
            'current_password' => 'Pass@123',
            'password' => 'Pass@2k22',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(Hash::check('Pass@123', $user->fresh()->password));
    }
}
