<?php

namespace App\Livewire\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

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
        $this->dispatch('open-project-modal');
    }

    public function create(): void
    {
        $this->reset(['project', 'name', 'description', 'color']);
        $this->dispatch('open-project-modal');
    }

    #[On('create-project')]
    public function onCreateEvent(): void
    {
        $this->create();
    }

    public function save(): void
    {
        $this->validate();
        $team = Auth::user()->currentTeam;
        if ($this->project) {
            $this->project->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'color' => $this->color ?: null,
            ]);
            ActivityLog::create([
                'team_id' => $team->id,
                'user_id' => Auth::id(),
                'action' => 'project.updated',
                'subject_type' => Project::class,
                'subject_id' => $this->project->id,
                'meta' => ['name' => $this->name],
            ]);
            $this->dispatch('toast', type: 'success', message: 'Project updated');
        } else {
            $project = Project::create([
                'team_id' => $team->id,
                'created_by' => Auth::id(),
                'name' => $this->name,
                'description' => $this->description ?: null,
                'color' => $this->color ?: null,
            ]);
            ActivityLog::create([
                'team_id' => $team->id,
                'user_id' => Auth::id(),
                'action' => 'project.created',
                'subject_type' => Project::class,
                'subject_id' => $project->id,
                'meta' => ['name' => $this->name],
            ]);
            $this->dispatch('toast', type: 'success', message: 'Project created');
        }
        $this->dispatch('close-project-modal');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('projects.form');
    }
}
