<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Team;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        // Get the first user and team for testing
        $user = User::first();
        $team = Team::first();
        
        if (!$user || !$team) {
            $this->command->info('No users or teams found. Please create a user and team first.');
            return;
        }

        // Get some existing tasks and projects for more realistic activities
        $task = Task::first();
        $project = Project::first();

        // Create sample activities
        $activities = [
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'team.joined',
                'meta' => [],
                'created_at' => now()->subMinutes(30),
            ],
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'project.created',
                'meta' => [
                    'project_name' => $project ? $project->name : 'Sample Project',
                ],
                'created_at' => now()->subMinutes(25),
            ],
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'task.created',
                'meta' => [
                    'task_title' => $task ? $task->title : 'Sample Task',
                    'project_name' => $project ? $project->name : 'Sample Project',
                ],
                'created_at' => now()->subMinutes(20),
            ],
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'task.updated',
                'meta' => [
                    'task_title' => $task ? $task->title : 'Sample Task',
                ],
                'created_at' => now()->subMinutes(15),
            ],
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'file.uploaded',
                'meta' => [
                    'file_name' => 'document.pdf',
                    'file_size' => 1024000,
                    'task_title' => $task ? $task->title : 'Sample Task',
                ],
                'created_at' => now()->subMinutes(10),
            ],
            [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'action' => 'task.moved',
                'meta' => [
                    'task_title' => $task ? $task->title : 'Sample Task',
                    'from_status' => 'todo',
                    'to_status' => 'in_progress',
                ],
                'created_at' => now()->subMinutes(5),
            ],
        ];

        foreach ($activities as $activity) {
            ActivityLog::create($activity);
        }

        $this->command->info('Sample activity log entries created successfully!');
    }
}
