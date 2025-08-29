<?php

namespace App\Livewire\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectForm extends Component
{
    public ?Project $project = null;
    public string $name = '';
    public string $description = '';
    public string $color = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:32'],
        ];
    }

    #[On('edit-project')]
    public function edit(int $id): void
    {
        $this->project = Project::findOrFail($id);
        $this->name = $this->project->name;
        $this->description = (string) $this->project->description;
        $this->color = (string) $this->project->color;
    // also trigger browser event so Alpine container can open
    // dispatch globally so the parent page listener can respond
    $this->dispatch('open-project-modal');
    }

    public function create(): void
    {
        $this->reset(['project', 'name', 'description', 'color']);
    // dispatch globally so the parent page listener can respond
    $this->dispatch('open-project-modal');
    }

    #[On('create-project')]
    public function onCreateEvent(): void
    {
        $this->create();
    }

    public function mount()
    {
        Log::info('ProjectForm mounted');
    }

    public function save()
    {
        Log::info('ProjectForm.save called', ['project_id' => optional($this->project)->id, 'name' => $this->name]);
        
        $this->validate();
        
        // Ensure we always have a team to attach the project to
        $user = Auth::user();
        $team = $user->currentTeam;
        if (!$team) {
            // Fallback to any team the user belongs to (without relying on dynamic relations)
            $team = \App\Models\Team::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->first();
            if ($team) {
                // Set as current to avoid future nulls
                DB::table('users')->where('id', $user->id)->update(['current_team_id' => $team->id]);
            }
        }
        if (!$team) {
            // As a last resort, create a personal team so project creation works
            $team = \Laravel\Jetstream\Jetstream::newTeamModel()->forceFill([
                'user_id' => $user->id,
                'name' => $user->name . "'s Team",
                'personal_team' => true,
            ]);
            $team->save();
            // Attach user to team and set current
            $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
            DB::table('users')->where('id', $user->id)->update(['current_team_id' => $team->id]);
        }

        $createdId = null;
        $toastMessage = null;
        Project::withoutSyncingToSearch(function () use ($team, &$createdId, &$toastMessage) {
            if ($this->project) {
                // authorize update
                if ($this->project->team_id !== $team->id) {
                    throw new AuthorizationException('Not allowed to update this project.');
                }
                $this->project->update([
                    'name' => $this->name,
                    'description' => $this->description ?: null,
                    'color' => $this->normalizedColor($this->color),
                ]);
        Log::info('Project updated', ['id' => $this->project->id]);
                ActivityLog::create([
                    'team_id' => $team->id,
                    'user_id' => Auth::id(),
                    'action' => 'project.updated',
                    'subject_type' => Project::class,
                    'subject_id' => $this->project->id,
                    'meta' => ['name' => $this->name],
                ]);
                $toastMessage = 'Project updated successfully';
            } else {
                $project = Project::create([
                    'team_id' => $team->id,
                    'created_by' => Auth::id(),
                    'name' => $this->name,
                    'description' => $this->description ?: null,
                    'color' => $this->normalizedColor($this->color) ?? '#6366f1',
                ]);
                $createdId = $project->id;
        Log::info('Project created', ['id' => $createdId]);
                ActivityLog::create([
                    'team_id' => $team->id,
                    'user_id' => Auth::id(),
                    'action' => 'project.created',
                    'subject_type' => Project::class,
                    'subject_id' => $project->id,
                    'meta' => ['name' => $this->name],
                ]);
                $toastMessage = 'Project created successfully';
            }
        });

        // Single success toast dispatch
        $this->dispatch('toast', type: 'success', message: $toastMessage);
        session()->flash('toast', ['type' => 'success', 'message' => $toastMessage]);
        
        // Close modal and request list refresh
        $this->dispatch('close-project-modal');
        $this->dispatch('refresh-projects', id: $createdId);
        
        // Reset form for next create
        $this->reset(['project', 'name', 'description', 'color']);
        
        // Use simple redirect without navigate to avoid DOM tree issues
        return $this->redirect('/projects');
    }

    private function normalizedColor(?string $value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        if ($v[0] !== '#') {
            $v = '#'.$v;
        }
        return $v;
    }

    public function render()
    {
        return view('projects.form');
    }
}
