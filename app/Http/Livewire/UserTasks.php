<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserTasks extends Component
{
    public $tasks = [];

    public function mount(): void
    {
        $this->loadTasks();
    }

    public function loadTasks(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->tasks = [];
            return;
        }

        $this->tasks = $user->assignedTasks()->with('creator')->latest()->get();
    }

    public function render()
    {
        return view('livewire.user-tasks');
    }
}
