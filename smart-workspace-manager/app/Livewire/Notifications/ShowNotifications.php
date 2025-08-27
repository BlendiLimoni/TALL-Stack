<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowNotifications extends Component
{
    public $notifications = [];

    public function mount()
    {
        $this->notifications = Auth::user()->notifications;
    }

    public function render()
    {
        return view('livewire.notifications.show-notifications');
    }
}