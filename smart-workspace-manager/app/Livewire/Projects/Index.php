<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $search = '';
    public int $bump = 0;

    #[On('refresh-projects')]
    public function refreshProjects(): void
    {
        Log::info('Projects.Index refreshProjects called');
        // Clear any active search so new items are visible, then trigger re-render
        $this->search = '';
        $this->bump++; // trigger re-render
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        if (!$team) {
            $team = Team::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->first();
            if ($team) {
                DB::table('users')->where('id', $user->id)->update(['current_team_id' => $team->id]);
            } else {
                // Create a personal team on the fly to avoid null errors for new users
                $team = Team::create([
                    'user_id' => $user->id,
                    'name' => $user->name."'s Team",
                    'personal_team' => true,
                ]);
                $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
                DB::table('users')->where('id', $user->id)->update(['current_team_id' => $team->id]);
            }
        }

        $projects = $team->projects()
            ->withCount(['tasks as tasks_todo_count' => fn($q) => $q->where('status','todo'),
                         'tasks as tasks_in_progress_count' => fn($q) => $q->where('status','in_progress'),
                         'tasks as tasks_done_count' => fn($q) => $q->where('status','done')])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description','like', "%{$this->search}%"))
            ->latest()
            ->get();

        Log::info('Projects.Index render', [
            'team_id' => $team->id,
            'search' => $this->search,
            'count' => $projects->count(),
        ]);

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }
}
