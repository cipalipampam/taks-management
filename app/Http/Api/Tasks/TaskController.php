<?php

namespace App\Http\Api\Tasks;

use App\Http\Api\Tasks\Requests\StoreTaskRequest;
use App\Http\Api\Tasks\Requests\UpdateTaskRequest;
use App\Http\Api\Tasks\Requests\UpdateTaskStatusRequest;
use App\Http\Api\Tasks\Resources\TaskResource;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\Cache\TaskCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * List tasks assigned to the current user (staff - My Tasks).
     */
    public function myTasks(Request $request): AnonymousResourceCollection
    {
        $tasks = TaskCacheService::getUserTasksList($request->user()->id);

        return TaskResource::collection($tasks);
    }

    /**
     * List tasks created by the current supervisor (Manage Staff Tasks).
     */
    public function supervisorTasks(Request $request): JsonResponse
    {
        if (! $request->user()->can('tasks.manage.staff')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $tasks = TaskCacheService::getSupervisorTasksList($request->user()->id);

        return response()->json(['data' => $tasks]);
    }

    /**
     * Update task status (for assignees with tasks.update-status).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource
    {
        $task->update(['status' => $request->validated('status')]);
        $task->load(['creator', 'assignees']);

        return new TaskResource($task);
    }

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
