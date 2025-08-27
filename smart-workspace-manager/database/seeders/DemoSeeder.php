<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $owner = User::factory()->create([
            'name' => 'Owner One',
            'email' => 'owner@example.com',
        ]);
        $admin = User::factory()->create([
            'name' => 'Admin Two',
            'email' => 'admin@example.com',
        ]);
        $member = User::factory()->create([
            'name' => 'Member Three',
            'email' => 'member@example.com',
        ]);

        // Team
    $team = Team::forceCreate([
            'user_id' => $owner->id,
            'name' => 'Acme Workspace',
            'personal_team' => false,
        ]);

    // Team owner is $owner via user_id. Attach others with roles.
    $admin->teams()->attach($team, ['role' => 'admin']);
    $member->teams()->attach($team, ['role' => 'member']);

        // Set current team for owner
        $owner->switchTeam($team);

        // Projects
        $colors = ['#6366f1', '#10b981', '#f59e0b'];
        $projects = collect(['Website Revamp', 'Mobile App', 'Marketing Sprint'])->map(function ($name, $i) use ($team, $owner, $colors) {
            return Project::create([
                'team_id' => $team->id,
                'created_by' => $owner->id,
                'name' => $name,
                'description' => 'Demo project: ' . $name,
                'color' => $colors[$i % count($colors)],
            ]);
        });

        // Tasks
        foreach ($projects as $project) {
            foreach (range(1, 7) as $i) {
                Task::create([
                    'project_id' => $project->id,
                    'created_by' => $owner->id,
                    'title' => "Task #$i",
                    'description' => 'This is a demo task',
                    'status' => collect(['todo', 'in_progress', 'done'])->random(),
                    'priority' => collect(['low', 'medium', 'high', 'urgent'])->random(),
                    'order' => $i,
                    'assigned_user_id' => [$owner->id, $admin->id, $member->id][($i - 1) % 3],
                    'due_date' => now()->addDays(rand(0, 20)),
                ]);
            }
        }

        ActivityLog::create([
            'team_id' => $team->id,
            'user_id' => $owner->id,
            'action' => 'seeded',
            'meta' => ['note' => 'Demo data seeded'],
        ]);
    }
}
