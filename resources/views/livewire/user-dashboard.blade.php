<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $stats = [];

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->stats = [];
            return;
        }

        $tasks = $user->assignedTasks()->get();
        $this->stats = [
            ['label' => 'Not Started', 'value' => $tasks->where('status', 'todo')->count(), 'desc' => 'Tasks waiting to be started', 'status' => 'todo'],
            ['label' => 'In Progress', 'value' => $tasks->where('status', 'doing')->count(), 'desc' => 'Tasks currently in progress', 'status' => 'doing'],
            ['label' => 'Completed', 'value' => $tasks->where('status', 'done')->count(), 'desc' => 'Finished tasks', 'status' => 'done'],
        ];
    }
}; ?>

<div
    wire:poll.5s="refreshStats"
    class="min-h-[calc(100vh-5rem)] bg-gradient-to-b from-zinc-50 to-white/80 px-4 py-6 dark:from-zinc-900 dark:to-zinc-950 sm:px-6 lg:px-8"
>
    <div class="mx-auto flex max-w-5xl flex-col gap-8">
        <div class="space-y-2">
            <flux:heading size="xl">Dashboard</flux:heading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($stats as $stat)
                @php
                    $accentClasses = match ($stat['status']) {
                        'todo' => 'border-s-sky-500/80 bg-sky-50/60 dark:border-s-sky-500/80 dark:bg-sky-950/40',
                        'doing' => 'border-s-amber-500/80 bg-amber-50/60 dark:border-s-amber-500/80 dark:bg-amber-950/40',
                        'done' => 'border-s-emerald-500/80 bg-emerald-50/60 dark:border-s-emerald-500/80 dark:bg-emerald-950/40',
                        default => 'border-s-zinc-300 bg-zinc-50/60 dark:border-s-zinc-600 dark:bg-zinc-900/40',
                    };
                @endphp

                <flux:card
                    class="relative overflow-hidden border border-zinc-100/80 bg-white/80 p-6 shadow-sm ring-1 ring-zinc-100/60 transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800/80 dark:bg-zinc-900/70 dark:ring-zinc-800/80"
                >
                    <div class="absolute inset-y-0 start-0 w-1 {{ $accentClasses }}"></div>

                    <div class="flex items-center gap-4 ps-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-zinc-50 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">
                            <flux:text size="xl" weight="bold">{{ $stat['value'] }}</flux:text>
                        </div>
                        <div class="min-w-0">
                            <flux:heading size="lg" class="truncate">
                                {{ $stat['label'] }}
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $stat['desc'] }}
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    </div>
</div>
