<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDeadlineSoonNotification;
use App\Notifications\TaskStatusChangedNotification;
use App\Services\Notification\TaskNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_assigned_sends_notification_to_assignees(): void
    {
        Notification::fake();

        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $actor->id]);
        $task->assignees()->attach($assignee->id);

        TaskNotificationService::taskAssigned($task, [$assignee->id], $actor);

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_task_assigned_with_empty_assignee_ids_does_nothing(): void
    {
        Notification::fake();

        $actor = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $actor->id]);

        TaskNotificationService::taskAssigned($task, [], $actor);

        Notification::assertNothingSent();
    }

    public function test_task_status_changed_sends_notification_to_assignees_and_creator(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $actor = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id, 'status' => 'todo']);
        $task->assignees()->attach($assignee->id);

        TaskNotificationService::taskStatusChanged($task, 'todo', 'doing', $actor);

        Notification::assertSentTo($creator, TaskStatusChangedNotification::class);
        Notification::assertSentTo($assignee, TaskStatusChangedNotification::class);
    }

    public function test_send_deadline_soon_notifications_sends_and_marks_task(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'deadline' => now()->addHours(12),
            'deadline_notified_at' => null,
            'status' => 'todo',
        ]);
        $task->assignees()->attach($assignee->id);

        $count = TaskNotificationService::sendDeadlineSoonNotifications(24);

        $this->assertSame(1, $count);
        Notification::assertSentTo($creator, TaskDeadlineSoonNotification::class);
        Notification::assertSentTo($assignee, TaskDeadlineSoonNotification::class);

        $task->refresh();
        $this->assertNotNull($task->deadline_notified_at);
    }

    public function test_send_deadline_soon_skips_done_tasks(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        Task::factory()->create([
            'created_by' => $creator->id,
            'deadline' => now()->addHours(12),
            'deadline_notified_at' => null,
            'status' => 'done',
        ]);

        $count = TaskNotificationService::sendDeadlineSoonNotifications(24);

        $this->assertSame(0, $count);
        Notification::assertNothingSent();
    }

    public function test_send_deadline_soon_skips_already_notified_tasks(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        Task::factory()->create([
            'created_by' => $creator->id,
            'deadline' => now()->addHours(12),
            'deadline_notified_at' => now(),
            'status' => 'todo',
        ]);

        $count = TaskNotificationService::sendDeadlineSoonNotifications(24);

        $this->assertSame(0, $count);
        Notification::assertNothingSent();
    }
}
