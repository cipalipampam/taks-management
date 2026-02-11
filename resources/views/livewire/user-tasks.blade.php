<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
      <div class="mt-8">
        <flux:heading size="lg">Tugas Saya</flux:heading>

        <div class="mt-4">
            @if(empty($tasks) || count($tasks) === 0)
                <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Belum ada tugas yang ditugaskan kepada Anda.</div>
            @else
                <div class="space-y-4">
                    @foreach($tasks as $task)
                        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $task->title }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">oleh {{ $task->creator?->name ?? '—' }}</div>
                                </div>
                                <div class="text-sm px-2 py-1 rounded text-white {{ $task->status === 'done' ? 'bg-green-600' : ($task->status === 'doing' ? 'bg-yellow-500' : 'bg-gray-500') }}">{{ ucfirst($task->status) }}</div>
                            </div>
                            @if($task->deadline)
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Deadline: {{ $task->deadline->format('d M Y H:i') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <livewire:stats-overview />
    </div>
</div>
