<?php

namespace Tests\Feature;

use App\Livewire\Projects\Kanban as ProjectKanban;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KanbanCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_edit_task(): void
    {
        $this->seed(DemoSeeder::class);
        $member = User::where('email', 'member@example.com')->firstOrFail();
        $project = Project::firstOrFail();
        $task = Task::where('project_id', $project->id)->firstOrFail();

        $this->actingAs($member);

        Livewire::test(ProjectKanban::class, ['project' => $project])
            ->call('saveTask', [
                'taskId' => $task->id,
                'data' => [
                    'title' => 'Updated Title',
                    'description' => (string) ($task->description ?? ''),
                    'due_date' => optional($task->due_date)->format('Y-m-d'),
                    'assigned_user_id' => $task->assigned_user_id,
                    'priority' => $task->priority,
                    'status' => $task->status,
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_task_but_member_cannot(): void
    {
        $this->seed(DemoSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $member = User::where('email', 'member@example.com')->firstOrFail();
        $project = Project::firstOrFail();
        $task = Task::where('project_id', $project->id)->firstOrFail();

        // Member cannot delete
        $this->actingAs($member);
        Livewire::test(ProjectKanban::class, ['project' => $project])
            ->call('deleteTask', $task->id)
            ->assertForbidden();

        // Admin can delete
        $this->actingAs($admin);
        Livewire::test(ProjectKanban::class, ['project' => $project])
            ->call('deleteTask', $task->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_reorder_task_updates_status_and_order(): void
    {
        $this->seed(DemoSeeder::class);
        $owner = User::where('email', 'owner@example.com')->firstOrFail();
        $project = Project::firstOrFail();
        $task = Task::where('project_id', $project->id)->firstOrFail();

        $this->actingAs($owner);

        Livewire::test(ProjectKanban::class, ['project' => $project])
            ->call('reorderTask', [
                'taskId' => $task->id,
                'status' => 'in_progress',
                'order' => 0,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
            'order' => 0,
        ]);
    }

    public function test_member_can_create_task(): void
    {
        $this->seed(DemoSeeder::class);
        $member = User::where('email', 'member@example.com')->firstOrFail();
        $project = Project::firstOrFail();

        $this->actingAs($member, 'web');

        Livewire::test(ProjectKanban::class, ['project' => $project])
            ->call('saveTask', [
                'taskId' => null,
                'data' => [
                    'title' => 'Brand New Task',
                    'description' => 'Created from test',
                    'due_date' => null,
                    'assigned_user_id' => null,
                    'priority' => 'medium',
                    'status' => 'todo',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Brand New Task',
            'status' => 'todo',
        ]);
    }
}
