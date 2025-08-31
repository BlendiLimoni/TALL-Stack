<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'filename',
        'stored_filename',
        'mime_type',
        'file_size',
        'disk',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected $appends = [
        'url',
        'formatted_size',
        'is_image',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/attachments/' . $this->stored_filename);
        }
        
        return route('attachments.download', $this->id);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    // Helper methods
    public function downloadResponse()
    {
        return response()->download(
            Storage::disk($this->disk)->path($this->stored_filename),
            $this->filename
        );
    }

    public function delete()
    {
        // Delete the file from storage
        if (Storage::disk($this->disk)->exists($this->stored_filename)) {
            Storage::disk($this->disk)->delete($this->stored_filename);
        }

        // Delete the database record
        return parent::delete();
    }
}
