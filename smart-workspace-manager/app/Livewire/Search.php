<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use Livewire\Component;

class Search extends Component
{
    public $query = '';
    public $results = [];

    public function updatedQuery()
    {
        if (strlen((string) $this->query) > 2) {
            try {
                $projects = Project::search((string) $this->query)->get();
                $tasks = Task::search((string) $this->query)->get();
                $this->results = $projects->concat($tasks);
            } catch (\Throwable $e) {
                // If the search index is inconsistent or fields contain arrays, avoid crashing the UI
                logger()->warning('Search failed', ['q' => $this->query, 'error' => $e->getMessage()]);
                $this->results = [];
            }
        } else {
            $this->results = [];
        }
    }

    public function render()
    {
        return view('livewire.search');
    }
}