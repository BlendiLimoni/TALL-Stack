<?php

use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Kanban as ProjectKanban;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Schema;

Route::get('/check-schema', function () {
    return response()->json(Schema::getColumnListing('tasks'));
});

use Illuminate\Support\Facades\DB;

Route::get('/fix-migrations', function () {
    DB::table('migrations')->where('migration', '2025_08_27_210710_add_due_date_to_tasks_table')->delete();
    return 'Migration record deleted.';
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/projects', ProjectsIndex::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectKanban::class)->where('project', '[0-9]+')->name('projects.show');
});

// Quick demo login to showcase the app (disabled in production)
Route::get('/demo', function () {
    if (app()->environment('production')) {
        abort(403);
    }

    $user = User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );

    // Ensure a demo team exists and user is attached
    $team = Team::firstOrCreate(
        ['name' => 'Demo Team', 'user_id' => $user->id],
        ['personal_team' => false]
    );
    $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);

    // Set current team for the user
    if ($user->current_team_id !== $team->id) {
        $user->forceFill(['current_team_id' => $team->id])->save();
    }

    // Seed a demo project and a few tasks if empty
    if ($team->projects()->count() === 0) {
        $project = Project::create([
            'team_id' => $team->id,
            'name' => 'Demo Project',
            'description' => 'A sample project to explore the Kanban board.',
            'created_by' => $user->id,
        ]);

        foreach ([
            ['title' => 'Plan Sprint', 'priority' => 'high', 'status' => 'todo', 'order' => 0],
            ['title' => 'Design Kanban UI', 'priority' => 'medium', 'status' => 'in_progress', 'order' => 0],
            ['title' => 'Implement Drag & Drop', 'priority' => 'urgent', 'status' => 'in_progress', 'order' => 1],
            ['title' => 'Write Feature Tests', 'priority' => 'low', 'status' => 'done', 'order' => 0],
        ] as $t) {
            Task::create([
                'project_id' => $project->id,
                'title' => $t['title'],
                'priority' => $t['priority'],
                'status' => $t['status'],
                'order' => $t['order'],
                'created_by' => $user->id,
            ]);
        }
    }

    Auth::login($user);

    return redirect()->route('projects.index');
})->name('demo.login');
