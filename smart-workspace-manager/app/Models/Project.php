<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'color',
        'created_by',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    /**
     * Scout indexing payload as strings only (TNTSearch requirement).
     */
    public function toSearchableArray(): array
    {
        $data = [
            'id' => (string) $this->id,
            'name' => (string) ($this->name ?? ''),
            'description' => (string) ($this->description ?? ''),
            'team' => (string) optional($this->team)->name,
            'creator' => (string) optional($this->creator)->name,
        ];

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
