<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.manage') || $user->can('tasks.manage.staff');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->can('tasks.manage')) {
            return true;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        if ($this->isAssignee($user, $task)) {
            return true;
        }

        return $user->can('tasks.manage.staff') && $this->isStaffAssignableTask($task);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('tasks.manage') || $user->can('tasks.manage.staff');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.manage') ||
            ($user->can('tasks.manage.staff') && $this->isStaffAssignableTask($task));
    }

    /**
     * Determine whether the user can update the task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        if ($user->can('tasks.manage')) {
            return true;
        }

        return $user->can('tasks.update-status') && $this->isAssignee($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.manage') ||
            ($user->can('tasks.manage.staff') && $this->isStaffAssignableTask($task));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->can('tasks.manage');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->can('tasks.manage');
    }

    protected function isAssignee(User $user, Task $task): bool
    {
        return $task->assignees()->whereKey($user->id)->exists();
    }

    protected function isStaffAssignableTask(Task $task): bool
    {
        return $task->assignees()
            ->where(function ($query): void {
                $query
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor']))
                    ->orWhereHas('permissions', fn ($q) => $q->where('name', 'tasks.update-status'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'tasks.update-status'));
            })
            ->exists();
    }
}
