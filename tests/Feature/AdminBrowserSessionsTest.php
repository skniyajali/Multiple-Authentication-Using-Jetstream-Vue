<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminBrowserSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_other_browser_sessions_can_be_logged_out()
    {
        $this->actingAs($user = Admin::factory()->create());

        $response = $this->delete('/admin/user/other-browser-sessions', [
            'password' => 'Pass@123',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
