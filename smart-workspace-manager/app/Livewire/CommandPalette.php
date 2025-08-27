<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class CommandPalette extends Component
{
    public $query = '';
    public $results = [];
    public $isOpen = false;

    public function updatedQuery()
    {
        if (strlen($this->query) > 0) {
            $this->results = Project::where('name', 'like', '%' . $this->query . '%')->get();
        } else {
            $this->results = [];
        }
    }

    public function openPalette()
    {
        $this->isOpen = true;
    }

    public function closePalette()
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
    }

    public function render()
    {
        return view('livewire.command-palette');
    }
}