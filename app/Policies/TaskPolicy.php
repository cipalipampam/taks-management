<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only admins can list all tasks.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Admins can view any task. Users can only view tasks assigned to them or created by them.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || 
               $user->id === $task->assigned_to || 
               $user->id === $task->created_by;
    }

    /**
     * Determine whether the user can create models.
     * Only admins can create tasks.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Only admins can update tasks (full edit).
z     */
    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the task status.
     * Admins can update any task status. Users can only update status of tasks assigned to them.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }
}
