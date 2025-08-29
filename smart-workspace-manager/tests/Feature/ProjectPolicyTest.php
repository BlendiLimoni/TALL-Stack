<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_owner_can_delete_project(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue($owner->can('delete', $project));
    }

    public function test_team_admin_can_delete_project(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        $admin = User::factory()->create();
        
        // Add admin to team with admin role
        $team->users()->attach($admin, ['role' => 'admin']);
        
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue($admin->can('delete', $project));
    }

    public function test_team_member_cannot_delete_project(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        $member = User::factory()->create();
        
        // Add member to team with member role
        $team->users()->attach($member, ['role' => 'member']);
        
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse($member->can('delete', $project));
    }

    public function test_non_team_member_cannot_delete_project(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        $outsider = User::factory()->create();
        
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse($outsider->can('delete', $project));
    }

    public function test_team_members_can_update_project(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;
        $member = User::factory()->create();
        
        // Add member to team
        $team->users()->attach($member, ['role' => 'member']);
        
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue($member->can('update', $project));
        $this->assertTrue($owner->can('update', $project));
    }
}
