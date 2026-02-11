<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UserNotifications extends Component
{
    use WithPagination;

    public $perPage = 10;

    protected $listeners = [
        'notificationRead' => 'markAsRead',
    ];

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        $user = Auth::user();
        // Jika user staff, kosongkan notifikasi

        if ($user->hasRole('staff')) {
            // Buat paginator kosong secara manual
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        } else {
            $notifications = $user->notifications()->latest()->paginate($this->perPage);
        }

        return view('livewire.user-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
