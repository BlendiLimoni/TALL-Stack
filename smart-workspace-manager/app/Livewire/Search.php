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
        if (strlen($this->query) > 2) {
            $projects = Project::search($this->query)->get();
            $tasks = Task::search($this->query)->get();
            $this->results = $projects->concat($tasks);
        } else {
            $this->results = [];
        }
    }

    public function render()
    {
        return view('livewire.search');
    }
}