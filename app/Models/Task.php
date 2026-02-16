<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
