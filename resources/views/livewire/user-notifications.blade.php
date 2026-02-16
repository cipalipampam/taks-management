<div
    class="min-w-0 bg-gradient-to-b from-zinc-50 to-white/80 px-4 py-6 dark:from-zinc-900 dark:to-zinc-950 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-3xl space-y-6">
        <header class="border-b border-zinc-200/70 pb-4 dark:border-zinc-800/70">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <flux:heading size="xl">Notifikasi</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Ringkasan aktivitas terbaru terkait tugas Anda.
                    </flux:text>
                </div>
                @if ($notifications->count() > 0)
                    <flux:badge color="zinc">
                        {{ $notifications->total() }} total
                    </flux:badge>
                @endif
            </div>
        </header>

        @if ($notifications->isEmpty())
            <flux:card class="flex flex-col items-center justify-center gap-3 border border-dashed border-zinc-200/80 bg-white/80 p-8 text-center dark:border-zinc-700/80 dark:bg-zinc-900/70">
                <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-200">
                    Tidak ada notifikasi
                </flux:heading>
                <flux:text class="max-w-md text-zinc-500 dark:text-zinc-400">
                    Notifikasi akan muncul di sini saat Anda di-assign tugas baru, status tugas berubah, atau deadline tugas mendekat.
                </flux:text>
            </flux:card>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $title = $notification->data['title'] ?? 'Tanpa judul';

                        [$accentClasses, $badgeColor, $badgeLabel, $line1] = match ($notification->type) {
                            'App\\Notifications\\TaskAssignedNotification' => [
                                'bg-sky-500',
                                'sky',
                                'Assigned',
                                'Tugas baru di-assign ke Anda.',
                            ],
                            'App\\Notifications\\TaskStatusChangedNotification' => [
                                'bg-amber-500',
                                'amber',
                                'Status',
                                'Status tugas berubah.',
                            ],
                            'App\\Notifications\\TaskDeadlineSoonNotification' => [
                                'bg-rose-500',
                                'rose',
                                'Deadline',
                                'Deadline tugas mendekat.',
                            ],
                            default => [
                                'bg-zinc-400',
                                'zinc',
                                'Info',
                                'Notifikasi sistem.',
                            ],
                        };
                    @endphp

                    <flux:card
                        wire:key="notification-{{ $notification->id }}"
                        class="relative overflow-hidden border border-zinc-100/80 bg-white/80 p-4 shadow-sm ring-1 ring-zinc-100/60 transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800/80 dark:bg-zinc-900/70 dark:ring-zinc-800/80"
                    >
                        <div class="absolute inset-y-0 start-0 w-1 {{ $accentClasses }}"></div>

                        <div class="flex items-start gap-4 ps-3">
                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-emerald-500' : 'bg-zinc-500' }}"></div>

                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <flux:badge :color="$badgeColor" size="xs">
                                                {{ $badgeLabel }}
                                            </flux:badge>

                                            <flux:text
                                                size="sm"
                                                class="text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{ $line1 }}
                                            </flux:text>
                                        </div>

                                        <flux:heading size="md" class="text-zinc-900 dark:text-zinc-50">
                                            {{ $title }}
                                        </flux:heading>

                                        @if ($notification->type === 'App\\Notifications\\TaskStatusChangedNotification')
                                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                Status: {{ $notification->data['old_status'] ?? '—' }}
                                                →
                                                {{ $notification->data['new_status'] ?? '—' }}
                                            </flux:text>

                                            @if (! empty($notification->data['changed_by_name']))
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-500">
                                                    Diubah oleh {{ $notification->data['changed_by_name'] }}
                                                </flux:text>
                                            @endif
                                        @elseif ($notification->type === 'App\\Notifications\\TaskAssignedNotification')
                                            @if (! empty($notification->data['assigned_by_name']))
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    Di-assign oleh {{ $notification->data['assigned_by_name'] }}
                                                </flux:text>
                                            @endif
                                            @if (! empty($notification->data['deadline']))
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-500">
                                                    Deadline: {{ $notification->data['deadline'] }}
                                                </flux:text>
                                            @endif
                                        @elseif ($notification->type === 'App\\Notifications\\TaskDeadlineSoonNotification')
                                            @if (! empty($notification->data['deadline']))
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    Deadline: {{ $notification->data['deadline'] }}
                                                </flux:text>
                                            @endif
                                            @if (! empty($notification->data['window_hours']))
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-500">
                                                    Jangka waktu: {{ $notification->data['window_hours'] }} jam
                                                </flux:text>
                                            @endif
                                        @endif
                                    </div>

                                    <div class="flex flex-col items-end gap-1">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </flux:text>

                                        @if ($isUnread)
                                            <flux:button
                                                wire:click="markAsRead('{{ $notification->id }}')"
                                                size="xs"
                                                variant="ghost"
                                                class="mt-1"
                                            >
                                                Tandai dibaca
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
