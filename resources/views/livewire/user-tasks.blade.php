<?php

use App\Models\Task;
use App\Services\Cache\TaskCacheService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
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

        $this->tasks = TaskCacheService::getUserTasksList($user->id);
    }

    public function updateTaskStatus(int $taskId, string $status): void
    {
        $user = Auth::user();
        if (! $user || ! $user->can('tasks.update-status')) {
            return;
        }

        $task = Task::query()
            ->where('id', $taskId)
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $task || ! in_array($status, ['todo', 'doing', 'done'], true)) {
            return;
        }

        $task->update(['status' => $status]);
        $this->loadTasks();
    }

    public function canUpdateStatus(): bool
    {
        $user = Auth::user();

        return $user && $user->can('tasks.update-status');
    }
}; ?>

<div
    wire:poll.5s="loadTasks"
    class="min-w-0 bg-gradient-to-b from-zinc-50 to-white/80 px-4 py-6 dark:from-zinc-900 dark:to-zinc-950 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-5xl space-y-6">
        <header class="border-b border-zinc-200/70 pb-3 dark:border-zinc-800/70">
            <flux:heading size="xl">My Tasks</flux:heading>
        </header>

        @if(empty($tasks) || count($tasks) === 0)
            <flux:card class="flex flex-col items-center justify-center gap-3 border border-dashed border-zinc-200/80 bg-white/80 p-8 text-center dark:border-zinc-700/80 dark:bg-zinc-900/70">
                <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-200">
                    No tasks yet
                </flux:heading>
                <flux:text class="max-w-md text-zinc-500 dark:text-zinc-400">
                    You do not have any assigned tasks yet. New tasks will appear here
                    once a supervisor or admin assigns them to you.
                </flux:text>
            </flux:card>
        @else
            <div class="space-y-4">
                @foreach($tasks as $task)
                    @php
                        $accentClasses = match ($task->status) {
                            'todo' => 'border-s-sky-500/80',
                            'doing' => 'border-s-amber-500/80',
                            'done' => 'border-s-emerald-500/80',
                            default => 'border-s-zinc-300',
                        };
                    @endphp

                    <flux:card
                        wire:key="task-{{ $task->id }}"
                        class="overflow-hidden border border-zinc-100/80 bg-white/80 ps-3 shadow-sm ring-1 ring-zinc-100/60 transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800/80 dark:bg-zinc-900/70 dark:ring-zinc-800/80"
                    >
                        <div class="flex h-full gap-0">
                            <div class="w-1 rounded-s-xl bg-gradient-to-b {{ $accentClasses }}"></div>

                            <div class="flex flex-1 flex-col gap-4 p-4 sm:flex-row sm:items-start sm:justify-between sm:p-5">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <flux:heading size="lg" class="truncate">
                                        {{ $task->title }}
                                    </flux:heading>
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Created by {{ $task->creator?->name ?? '—' }}
                                    </flux:text>
                                    @if($task->deadline)
                                        <flux:text class="block text-sm text-zinc-500 dark:text-zinc-400">
                                            Deadline: {{ $task->deadline->format('d M Y H:i') }}
                                        </flux:text>
                                    @endif
                                </div>

                                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-col sm:items-end">
                                    @if($task->status === 'todo')
                                        <flux:badge color="zinc">{{ __('Todo') }}</flux:badge>
                                        @if($this->canUpdateStatus())
                                            <flux:button
                                                size="sm"
                                                variant="primary"
                                                wire:click="updateTaskStatus({{ $task->id }}, 'doing')"
                                                wire:loading.attr="disabled"
                                            >
                                                Start
                                            </flux:button>
                                        @endif
                                    @elseif($task->status === 'doing')
                                        <flux:badge color="amber">{{ __('Doing') }}</flux:badge>
                                        @if($this->canUpdateStatus())
                                            <flux:button
                                                size="sm"
                                                variant="primary"
                                                wire:click="updateTaskStatus({{ $task->id }}, 'done')"
                                                wire:loading.attr="disabled"
                                            >
                                                Complete
                                            </flux:button>
                                        @endif
                                    @else
                                        <flux:badge color="green">{{ __('Done') }}</flux:badge>
                                        @if($this->canUpdateStatus())
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                wire:click="updateTaskStatus({{ $task->id }}, 'doing')"
                                                wire:loading.attr="disabled"
                                            >
                                                Reopen
                                            </flux:button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    </div>
</div>

