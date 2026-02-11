<x-filament::widget>
    <x-slot name="header">
        <h3 class="text-lg font-bold">Notifikasi Admin</h3>
    </x-slot>
    <ul class="divide-y divide-gray-200">
        @forelse ($this->getNotifications() as $notification)
            <li class="py-2 flex items-start {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="flex-1">
                    <div class="font-semibold">
                        @if ($notification->type === 'App\\Notifications\\TaskAssignedNotification')
                            Tugas baru di-assign: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @elseif ($notification->type === 'App\\Notifications\\TaskStatusChangedNotification')
                            Status tugas berubah: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @elseif ($notification->type === 'App\\Notifications\\TaskDeadlineSoonNotification')
                            Deadline tugas mendekat: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @else
                            Notifikasi
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            </li>
        @empty
            <li class="py-2 text-gray-500">Tidak ada notifikasi.</li>
        @endforelse
    </ul>
</x-filament::widget>
<x-filament::widget>
    <x-slot name="header">
        <h3 class="text-lg font-bold">Notifikasi Admin</h3>
    </x-slot>
    <ul class="divide-y divide-gray-200">
        @forelse ($this->getNotifications() as $notification)
            <li class="py-2 flex items-start {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="flex-1">
                    <div class="font-semibold">
                        @if ($notification->type === 'App\\Notifications\\TaskAssignedNotification')
                            Tugas baru di-assign: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @elseif ($notification->type === 'App\\Notifications\\TaskStatusChangedNotification')
                            Status tugas berubah: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @elseif ($notification->type === 'App\\Notifications\\TaskDeadlineSoonNotification')
                            Deadline tugas mendekat: <span class="text-blue-600">{{ $notification->data['title'] ?? '' }}</span>
                        @else
                            Notifikasi
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            </li>
        @empty
            <li class="py-2 text-gray-500">Tidak ada notifikasi.</li>
        @endforelse
    </ul>
</x-filament::widget>
