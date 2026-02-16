<?php

namespace App\Http\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * List staff/supervisor users for assignee dropdown (supervisor only).
     */
    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->user()->can('tasks.manage.staff')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $users = User::query()
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor']))
                    ->orWhereHas('permissions', fn ($q) => $q->where('name', 'tasks.update-status'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'tasks.update-status'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $users]);
    }
}
