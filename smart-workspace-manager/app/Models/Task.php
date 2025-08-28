<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'due_date',
        'assigned_user_id',
        'priority',
        'order',
        'created_by',
        'estimated_hours',
        'actual_hours',
        'depends_on',
        'labels',
        'completion_percentage',
        'archived_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'depends_on' => 'array',
        'labels' => 'array',
        'archived_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    // Helper methods
    public function getTotalTimeSpentAttribute(): int
    {
        return $this->timeEntries()->sum('duration_minutes');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function getCompletedSubtasksCountAttribute(): int
    {
        return $this->subtasks()->where('is_completed', true)->count();
    }

    public function getTotalSubtasksCountAttribute(): int
    {
        return $this->subtasks()->count();
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'text-green-600 bg-green-100',
            'medium' => 'text-yellow-600 bg-yellow-100',
            'high' => 'text-orange-600 bg-orange-100',
            'urgent' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'todo' => 'text-gray-600 bg-gray-100',
            'in_progress' => 'text-blue-600 bg-blue-100',
            'done' => 'text-green-600 bg-green-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    /**
     * Customize Scout indexing payload to avoid arrays (TNTSearch expects strings).
     */
    public function toSearchableArray(): array
    {
        $labels = $this->labels ?? [];
        if (is_array($labels)) {
            // Flatten nested labels just in case
            $labels = collect($labels)->flatten()->filter()->all();
        }

        $data = [
            'id' => (string) $this->id,
            'title' => (string) ($this->title ?? ''),
            'description' => (string) ($this->description ?? ''),
            'priority' => (string) ($this->priority ?? ''),
            'status' => (string) ($this->status ?? ''),
            'labels' => is_array($labels) ? implode(', ', $labels) : (string) $labels,
            'assignee' => (string) optional($this->assignee)->name,
            'project' => (string) optional($this->project)->name,
        ];

        // Ensure all values are strings to satisfy TNTSearch tokenizer
        return array_map(static function ($v) {
            if (is_array($v)) {
                return implode(' ', $v);
            }
            if (is_bool($v) || is_int($v) || is_float($v)) {
                return (string) $v;
            }
            return (string) ($v ?? '');
        }, $data);
    }
}
