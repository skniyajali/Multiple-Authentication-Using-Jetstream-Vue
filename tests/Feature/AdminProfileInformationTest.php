<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_information_can_be_updated()
    {
        $this->actingAs($user = Admin::factory()->create());

        $response = $this->put('admin/user/profile-information', [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }
}
