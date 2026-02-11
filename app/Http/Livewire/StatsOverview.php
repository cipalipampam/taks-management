<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;

class StatsOverview extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $totalTasks = Task::count();
        $todoTasks = Task::where('status', 'todo')->count();
        $doingTasks = Task::where('status', 'doing')->count();
        $doneTasks = Task::where('status', 'done')->count();
        $totalUsers = User::count();

        $this->stats = [
            ['label' => 'Total Tasks', 'value' => $totalTasks, 'desc' => 'All tasks in the system'],
            ['label' => 'To Do', 'value' => $todoTasks, 'desc' => 'Pending tasks'],
            ['label' => 'In Progress', 'value' => $doingTasks, 'desc' => 'Tasks being worked on'],
            ['label' => 'Completed', 'value' => $doneTasks, 'desc' => 'Finished tasks'],
            ['label' => 'Total Users', 'value' => $totalUsers, 'desc' => 'Registered users'],
            ['label' => 'Completion Rate', 'value' => $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) . '%' : '0%', 'desc' => 'Tasks completed'],
        ];
    }

    public function render()
    {
        return view('livewire.stats-overview');
    }
}
