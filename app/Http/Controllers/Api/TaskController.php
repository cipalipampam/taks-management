<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * List tasks visible to the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if (! Gate::allows('viewAny', Task::class)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = Task::query()
            ->visibleBy($request->user())
            ->with(['creator', 'assignees'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $tasks = $query->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a new task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $assignees = $data['assignees'] ?? [];
        unset($data['assignees']);

        $data['created_by'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'todo';

        $task = Task::create($data);
        $task->assignees()->sync($assignees);
        $task->load(['creator', 'assignees']);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Show a single task.
     */
    public function show(Request $request, Task $task): TaskResource|JsonResponse
    {
        if (! Gate::allows('view', $task)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $task->load(['creator', 'assignees']);

        return new TaskResource($task);
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $data = $request->validated();
        $assignees = $data['assignees'] ?? null;
        if (array_key_exists('assignees', $data)) {
            unset($data['assignees']);
        }

        $task->update($data);
        if ($assignees !== null) {
            $task->assignees()->sync($assignees);
        }
        $task->load(['creator', 'assignees']);

        return new TaskResource($task);
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! Gate::allows('delete', $task)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $task->delete();

        return response()->json(null, 204);
    }
}
