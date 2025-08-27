<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kanban_page_loads_for_team_member(): void
    {
        $this->seed(DemoSeeder::class);
        $user = User::first();
        $project = Project::first();

        $this->actingAs($user);
        $resp = $this->get(route('projects.show', $project));
        $resp->assertOk();
        $resp->assertSee('To Do');
        $resp->assertSee('In Progress');
        $resp->assertSee('Done');
    }
}
