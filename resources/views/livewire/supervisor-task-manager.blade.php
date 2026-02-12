<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $tasks = [];

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingTaskId = null;
    public ?int $deletingTaskId = null;

    public string $title = '';
    public string $description = '';
    public string $status = 'todo';
    public ?string $deadline = null;
    public array $assignees = [];

    public function mount(): void
    {
        if (! $this->canAccess()) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }
        $this->loadTasks();
    }

    public function canAccess(): bool
    {
        return (bool) Auth::user()?->can('tasks.manage.staff');
    }

    public function loadTasks(): void
    {
        if (! $this->canAccess()) {
            return;
        }
        $this->tasks = $this->tasksQuery()->with(['creator', 'assignees'])->latest()->get()->toArray();
    }

    protected function tasksQuery(): Builder
    {
        return Task::query()
            ->where('created_by', Auth::id())
            ->whereHas(
                'assignees',
                fn (Builder $builder) => $builder->where(function (Builder $query): void {
                    $query
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor']))
                        ->orWhereHas('permissions', fn ($q) => $q->where('name', 'tasks.update-status'))
                        ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'tasks.update-status'));
                })
            );
    }

    public function getStaffUsersProperty(): \Illuminate\Support\Collection
    {
        return User::query()
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor']))
                    ->orWhereHas('permissions', fn ($q) => $q->where('name', 'tasks.update-status'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'tasks.update-status'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEdit(int $id): void
    {
        $task = $this->tasksQuery()->find($id);
        if (! $task) {
            return;
        }
        $this->editingTaskId = $id;
        $this->title = $task->title;
        $this->description = (string) $task->description;
        $this->status = $task->status;
        $this->deadline = $task->deadline?->format('Y-m-d\TH:i');
        $this->assignees = $task->assignees->pluck('id')->values()->all();
        $this->showEditModal = true;
    }

    public function saveTask(): void
    {
        if (! $this->canAccess()) {
            return;
        }
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done'],
            'deadline' => ['nullable', 'date'],
            'assignees' => ['array'],
            'assignees.*' => ['integer', 'exists:users,id'],
        ]);

        $assigneeIds = collect($this->assignees)
            ->map(static fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline ? \Carbon\Carbon::parse($this->deadline) : null,
            'created_by' => Auth::id(),
        ]);
        $task->assignees()->sync($assigneeIds);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->loadTasks();
    }

    public function updateTask(): void
    {
        if (! $this->canAccess() || ! $this->editingTaskId) {
            return;
        }
        $task = $this->tasksQuery()->find($this->editingTaskId);
        if (! $task) {
            return;
        }

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done'],
            'deadline' => ['nullable', 'date'],
            'assignees' => ['array'],
            'assignees.*' => ['integer', 'exists:users,id'],
        ]);

        $assigneeIds = collect($this->assignees)
            ->map(static fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $task->update([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline ? \Carbon\Carbon::parse($this->deadline) : null,
        ]);
        $task->assignees()->sync($assigneeIds);

        $this->showEditModal = false;
        $this->editingTaskId = null;
        $this->resetForm();
        $this->loadTasks();
    }

    public function confirmDelete(int $id): void
    {
        if ($this->tasksQuery()->where('id', $id)->exists()) {
            $this->deletingTaskId = $id;
            $this->showDeleteModal = true;
        }
    }

    public function deleteTask(): void
    {
        if (! $this->canAccess() || ! $this->deletingTaskId) {
            return;
        }
        $task = $this->tasksQuery()->find($this->deletingTaskId);
        if ($task) {
            $task->assignees()->detach();
            $task->delete();
        }
        $this->showDeleteModal = false;
        $this->deletingTaskId = null;
        $this->loadTasks();
    }

    protected function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->status = 'todo';
        $this->deadline = null;
        $this->assignees = [];
    }

    public function getTaskForDeleteProperty(): ?array
    {
        if (! $this->deletingTaskId) {
            return null;
        }
        $task = $this->tasksQuery()->find($this->deletingTaskId);

        return $task ? ['id' => $task->id, 'title' => $task->title] : null;
    }
}; ?>

<div
    wire:poll.5s="loadTasks"
    class="min-w-0 bg-gradient-to-b from-zinc-50 to-white/80 px-4 py-6 dark:from-zinc-900 dark:to-zinc-950 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-6xl space-y-8">
        <header class="flex flex-col gap-3 border-b border-zinc-200/70 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800/70">
            <div>
                <flux:heading size="xl">Manage Staff Tasks</flux:heading>
            </div>
            <flux:button
                variant="primary"
                wire:click="openCreate"
                wire:loading.attr="disabled"
            >
                + Create Task
            </flux:button>
        </header>

        @if(empty($tasks))
            <flux:card class="flex flex-col items-center justify-center gap-3 border border-dashed border-zinc-200/80 bg-white/80 p-8 text-center shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900/70">
                <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-200">
                    No tasks yet
                </flux:heading>
                <flux:text class="max-w-md text-zinc-500 dark:text-zinc-400">
                    Start by creating a new task and assigning it to one or more staff members or supervisors.
                </flux:text>
            </flux:card>
        @else
            <div class="space-y-3">
                @foreach($tasks as $task)
                    @php
                        $accent = match ($task['status']) {
                            'todo' => 'bg-zinc-200 dark:bg-zinc-700',
                            'doing' => 'bg-amber-400/80',
                            'done' => 'bg-emerald-400/80',
                            default => 'bg-zinc-400/80',
                        };
                    @endphp

                    <flux:card
                        wire:key="card-{{ $task['id'] }}"
                        class="relative rounded-2xl border border-zinc-800/60 bg-zinc-800/50 px-5 py-4 shadow-sm ring-1 ring-zinc-700/50 dark:border-zinc-700/60 dark:bg-zinc-900/80 dark:ring-zinc-700/50"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-bold text-white">
                                    {{ $task['title'] }}
                                </h3>

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-300">
                                    @if($task['status'] === 'todo')
                                        <flux:badge color="zinc" class="!bg-zinc-600/80 !text-white">To Do</flux:badge>
                                    @elseif($task['status'] === 'doing')
                                        <flux:badge color="amber">Doing</flux:badge>
                                    @else
                                        <flux:badge color="green">Done</flux:badge>
                                    @endif
                                    <span class="h-1 w-1 shrink-0 rounded-full bg-zinc-500" aria-hidden="true"></span>
                                    <span>
                                        {{ $task['deadline'] ? \Carbon\Carbon::parse($task['deadline'])->format('d M Y, H:i') : 'No deadline' }}
                                    </span>
                                </div>

                                <div class="mt-2 space-y-0.5 text-sm text-zinc-400">
                                    <p>
                                        Assigned to
                                        <span class="font-medium text-zinc-200">
                                            {{ collect($task['assignees'])->pluck('name')->implode(', ') ?: 'No assignees' }}
                                        </span>
                                    </p>
                                    <p>
                                        Created by
                                        <span class="font-medium text-zinc-200">
                                            {{ $task['creator']['name'] ?? 'Unknown' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <flux:dropdown position="bottom" align="end" class="shrink-0">
                                <flux:button
                                    icon="ellipsis-horizontal"
                                    variant="ghost"
                                    size="xs"
                                    class="rounded-full text-zinc-400 hover:bg-zinc-700/80 hover:text-white"
                                />
                                <flux:menu class="min-w-32 rounded-lg border border-zinc-700 bg-zinc-800/95 py-1 dark:bg-zinc-900">
                                    <flux:menu.item
                                        icon="pencil-square"
                                        wire:click="openEdit({{ $task['id'] }})"
                                        wire:loading.attr="disabled"
                                    >
                                        Edit
                                    </flux:menu.item>
                                    <flux:menu.item
                                        icon="trash"
                                        variant="danger"
                                        wire:click="confirmDelete({{ $task['id'] }})"
                                        wire:loading.attr="disabled"
                                    >
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        @if(! empty(trim((string) ($task['description'] ?? ''))))
                            <div class="mt-3 border-t border-zinc-600/80 pt-3 dark:border-zinc-700/80">
                                <p class="text-sm leading-relaxed text-zinc-300 whitespace-pre-wrap line-clamp-4">
                                    {{ $task['description'] }}
                                </p>
                            </div>
                        @endif
                    </flux:card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Create Task Modal --}}
    <flux:modal name="create-task" wire:model="showCreateModal" class="max-w-3xl">
        <form wire:submit.prevent="saveTask" class="space-y-6">
            <div class="space-y-1.5">
                <flux:heading size="lg">Create Task</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    Define the task details and assign it to your team.
                </flux:text>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="title" placeholder="Task title" />
                        <flux:error name="title" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Description (optional)" rows="4" />
                        <flux:error name="description" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Status</flux:label>
                            <select wire:model="status" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="todo">To Do</option>
                                <option value="doing">Doing</option>
                                <option value="done">Done</option>
                            </select>
                            <flux:error name="status" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Deadline (optional)</flux:label>
                            <flux:input type="datetime-local" wire:model="deadline" />
                            <flux:error name="deadline" />
                        </flux:field>
                    </div>
                </div>

                <div class="space-y-3 rounded-xl border border-dashed border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                    <div class="space-y-1">
                        <flux:heading size="sm">Assignees</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            Select one or more staff members or supervisors who will be responsible for this task.
                        </flux:text>
                    </div>
                    <flux:field>
                        <flux:label class="sr-only">Assign to staff/supervisors</flux:label>
                        <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-zinc-300 bg-zinc-950/40 p-2 text-sm dark:border-zinc-700">
                            @foreach($this->staffUsers as $user)
                                <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-xs text-zinc-200 hover:bg-zinc-800/80">
                                    <input
                                        type="checkbox"
                                        value="{{ $user->id }}"
                                        wire:model="assignees"
                                        class="h-3.5 w-3.5 rounded border-zinc-500 bg-zinc-900 text-zinc-100"
                                    >
                                    <span class="truncate">{{ $user->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="assignees" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showCreateModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Task Modal --}}
    <flux:modal name="edit-task" wire:model="showEditModal" class="max-w-3xl">
        <form wire:submit.prevent="updateTask" class="space-y-6">
            <div class="space-y-1.5">
                <flux:heading size="lg">Edit Task</flux:heading>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="title" placeholder="Task title" />
                        <flux:error name="title" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Description (optional)" rows="4" />
                        <flux:error name="description" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Status</flux:label>
                            <select wire:model="status" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="todo">To Do</option>
                                <option value="doing">Doing</option>
                                <option value="done">Done</option>
                            </select>
                            <flux:error name="status" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Deadline (optional)</flux:label>
                            <flux:input type="datetime-local" wire:model="deadline" />
                            <flux:error name="deadline" />
                        </flux:field>
                    </div>
                </div>

                <div class="space-y-3 rounded-xl border border-dashed border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                    <div class="space-y-1">
                        <flux:heading size="sm">Assignees</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            Adjust which staff members or supervisors are assigned to this task.
                        </flux:text>
                    </div>
                    <flux:field>
                        <flux:label class="sr-only">Assign to staff/supervisors</flux:label>
                        <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-zinc-300 bg-zinc-950/40 p-2 text-sm dark:border-zinc-700">
                            @foreach($this->staffUsers as $user)
                                <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-xs text-zinc-200 hover:bg-zinc-800/80">
                                    <input
                                        type="checkbox"
                                        value="{{ $user->id }}"
                                        wire:model="assignees"
                                        class="h-3.5 w-3.5 rounded border-zinc-500 bg-zinc-900 text-zinc-100"
                                    >
                                    <span class="truncate">{{ $user->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="assignees" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-task" wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-5">
            <div class="space-y-1.5">
                <flux:heading size="lg">Delete Task</flux:heading>
                @if($this->taskForDelete)
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Are you sure you want to delete the task &quot;{{ $this->taskForDelete['title'] }}&quot;? This action cannot be undone.
                    </flux:text>
                @endif
            </div>
            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deleteTask">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
