<?php

namespace Tests\Feature;

use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\ProjectForm;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->team->users()->attach($this->user->id, ['role' => 'admin']);
        $this->user->forceFill(['current_team_id' => $this->team->id])->save();
    }

    public function test_can_delete_project_from_form()
    {
        $this->actingAs($this->user);

        // Create a project
        $project = Project::factory()->create([
            'team_id' => $this->team->id,
            'created_by' => $this->user->id,
            'name' => 'Test Project'
        ]);

        // Add some tasks to verify cascade delete
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $this->user->id,
        ]);

        // Mount the ProjectForm component with the project
        $component = Livewire::test(ProjectForm::class)
            ->set('project', $project)
            ->set('name', $project->name)
            ->set('description', $project->description)
            ->set('color', $project->color);

        // Call delete method
        $component->call('delete')
            ->assertRedirect('/projects');

        // Verify project and associated tasks were deleted
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'project.deleted',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);
    }

    public function test_can_delete_project_from_index()
    {
        $this->actingAs($this->user);

        // Create a project
        $project = Project::factory()->create([
            'team_id' => $this->team->id,
            'created_by' => $this->user->id,
            'name' => 'Test Project'
        ]);

        // Add some tasks to verify cascade delete
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $this->user->id,
        ]);

        // Mount the ProjectsIndex component
        $component = Livewire::test(ProjectsIndex::class);

        // Call delete method
        $component->call('deleteProject', $project->id);

        // Verify project and associated tasks were deleted
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'project.deleted',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
        ]);
    }

    public function test_cannot_delete_project_from_different_team()
    {
        $this->actingAs($this->user);

        // Create a project for a different team
        $otherTeam = Team::factory()->create();
        $project = Project::factory()->create([
            'team_id' => $otherTeam->id,
            'created_by' => $this->user->id,
            'name' => 'Other Team Project'
        ]);

        // Mount the ProjectsIndex component
        $component = Livewire::test(ProjectsIndex::class);

        // Try to delete the project from different team
        $component->call('deleteProject', $project->id);

        // Verify project was NOT deleted
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
