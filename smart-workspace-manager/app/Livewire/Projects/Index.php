<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $search = '';

    public function render()
    {
    $team = Auth::user()->currentTeam;
    $projects = $team->projects()
            ->withCount(['tasks as tasks_todo_count' => fn($q) => $q->where('status','todo'),
                         'tasks as tasks_in_progress_count' => fn($q) => $q->where('status','in_progress'),
                         'tasks as tasks_done_count' => fn($q) => $q->where('status','done')])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description','like', "%{$this->search}%"))
            ->latest()
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }
}
