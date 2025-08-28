<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $this->seed(DemoSeeder::class);
        $user = User::first();

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));
        
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Total Tasks');
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertRedirect(route('login'));
    }
}
