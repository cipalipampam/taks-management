<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, LogsActivity;

    protected static array $recordEvents = ['created'];

    protected $fillable = [
        'title',
        'description',
        'status',
        'deadline',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'deadline_notified_at' => 'datetime',
        ];
    }

    /**
     * Get task detail from cache or DB.
     */
    public static function findCached(int $id, int $ttl = 60): ?self
    {
        return Cache::remember("task_detail_{$id}", $ttl, function () use ($id) {
            return static::with('assignees')->find($id);
        });
    }

    /**
     * Get assignees for this task from cache.
     */
    public function assigneesCached(int $ttl = 60)
    {
        $key = "task_{$this->id}_assignees";

        return Cache::remember($key, $ttl, function () {
            return $this->assignees()->get();
        });
    }

    /**
     * Get reference statuses for tasks (cached).
     */
    public static function statusesCached(int $ttl = 3600): array
    {
        return Cache::remember('task_statuses', $ttl, function () {
            // If statuses are static, define here. Replace if you have a model.
            return ['todo', 'doing', 'done'];
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('task')
            ->logOnly(['title', 'status', 'deadline', 'created_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "task.$eventName");
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }
}
