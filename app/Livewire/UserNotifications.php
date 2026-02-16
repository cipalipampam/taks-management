<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

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
        $notifications = $user->notifications()->latest()->paginate($this->perPage);

        return view('livewire.user-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
