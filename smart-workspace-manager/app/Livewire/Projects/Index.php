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

    #[On('delete-project')]
    public function deleteProject(int $id): void
    {
        Log::info('Projects.Index deleteProject called', ['project_id' => $id]);
        
        $user = Auth::user();
        $team = $user->currentTeam;
        
        if (!$team) {
            $this->dispatch('toast', type: 'error', message: 'No team found');
            return;
        }

        // Find the project and verify ownership
        $project = Project::where('id', $id)->where('team_id', $team->id)->first();
        
        if (!$project) {
            $this->dispatch('toast', type: 'error', message: 'Project not found or not authorized');
            return;
        }

        // Check authorization using the policy
        try {
            $this->authorize('delete', $project);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('toast', type: 'error', message: 'You do not have permission to delete this project. Only team owners and admins can delete projects.');
            return;
        }

        $projectName = $project->name;

        // Log deletion activity before deleting
        \App\Models\ActivityLog::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'action' => 'project.deleted',
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'meta' => ['name' => $projectName],
        ]);

        // Delete the project
        $project->delete();
        
        Log::info('Project deleted from index', ['id' => $id, 'name' => $projectName]);

        // Show success message and refresh the list
        $this->dispatch('toast', type: 'success', message: "Project '{$projectName}' deleted successfully");
        $this->refreshProjects();
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
