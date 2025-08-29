<?php

namespace Tests\Feature;

use App\Livewire\Projects\Kanban;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function team_owner_can_assign_tasks_to_themselves()
    {
        // Create a team owner
        $owner = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $owner->id]);
        
        // Create a project for the team
        $project = Project::factory()->create(['team_id' => $team->id]);
        
        // Set the owner as the current team
        $owner->switchTeam($team);
        
        // Act as the team owner
        $this->actingAs($owner);
        
        // Test the Kanban component includes the owner in team users
        $component = Livewire::test(Kanban::class, ['project' => $project]);
        
        // Check that the component renders without errors
        $component->assertOk();
        
        // Check that the rendered view includes the owner in teamUsers
        $teamUsers = $component->viewData('teamUsers');
        $this->assertTrue($teamUsers->contains('id', $owner->id), 'Team owner should be included in team users for task assignment');
        
        // Verify owner details are correct
        $ownerInList = $teamUsers->firstWhere('id', $owner->id);
        $this->assertEquals($owner->name, $ownerInList->name);
        $this->assertEquals($owner->email, $ownerInList->email);
    }

    /** @test */
    public function team_owner_and_members_are_both_available_for_assignment()
    {
        // Create a team owner
        $owner = User::factory()->create(['name' => 'Team Owner']);
        $team = Team::factory()->create(['user_id' => $owner->id]);
        
        // Create team members
        $member1 = User::factory()->create(['name' => 'Member One']);
        $member2 = User::factory()->create(['name' => 'Member Two']);
        
        // Add members to team
        $team->users()->attach($member1, ['role' => 'member']);
        $team->users()->attach($member2, ['role' => 'admin']);
        
        // Create a project for the team
        $project = Project::factory()->create(['team_id' => $team->id]);
        
        // Set the owner as the current team
        $owner->switchTeam($team);
        
        // Act as the team owner
        $this->actingAs($owner);
        
        // Test the Kanban component
        $component = Livewire::test(Kanban::class, ['project' => $project]);
        
        // Check that all users are available for assignment
        $teamUsers = $component->viewData('teamUsers');
        
        $this->assertTrue($teamUsers->contains('id', $owner->id), 'Team owner should be available for assignment');
        $this->assertTrue($teamUsers->contains('id', $member1->id), 'Team member should be available for assignment');
        $this->assertTrue($teamUsers->contains('id', $member2->id), 'Team admin should be available for assignment');
        
        // Verify the list is sorted by name
        $names = $teamUsers->pluck('name')->toArray();
        $sortedNames = collect($names)->sort()->values()->toArray();
        $this->assertEquals($sortedNames, $names, 'Team users should be sorted by name');
    }

    /** @test */
    public function owner_can_create_task_assigned_to_themselves()
    {
        // Create a team owner
        $owner = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $owner->id]);
        
        // Create a project for the team
        $project = Project::factory()->create(['team_id' => $team->id]);
        
        // Set the owner as the current team
        $owner->switchTeam($team);
        
        // Act as the team owner
        $this->actingAs($owner);
        
        // Create a task assigned to the owner
        $component = Livewire::test(Kanban::class, ['project' => $project])
            ->call('saveTask', [
                'taskId' => null,
                'data' => [
                    'title' => 'Test Task for Owner',
                    'description' => 'Testing owner assignment',
                    'status' => 'todo',
                    'priority' => 'medium',
                    'assigned_user_id' => $owner->id,
                    'due_date' => null,
                ]
            ]);
        
        // Verify the task was created and assigned to the owner
        $task = Task::where('title', 'Test Task for Owner')->first();
        $this->assertNotNull($task, 'Task should be created');
        $this->assertEquals($owner->id, $task->assigned_user_id, 'Task should be assigned to the team owner');
        $this->assertEquals($project->id, $task->project_id, 'Task should belong to the correct project');
    }
}
