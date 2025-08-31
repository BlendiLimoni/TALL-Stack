<?php

namespace App\Livewire;

use App\Models\Attachment;
use App\Livewire\LiveActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public Model $model; // The model to attach files to (Task, Project, etc.)
    public $files = [];
    public $uploading = false;

    protected $listeners = ['refreshAttachments' => '$refresh'];

    public function mount(Model $model)
    {
        $this->model = $model;
    }

    public function updatedFiles()
    {
        $this->validate([
            'files.*' => 'file|max:10240', // 10MB max per file
        ]);

        $this->uploading = true;
        $this->uploadFiles();
    }

    public function uploadFiles()
    {
        try {
            foreach ($this->files as $file) {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;

                // Store file
                $path = $file->storeAs('attachments', $filename, 'public');

                if (!$path) {
                    throw new \Exception('Failed to store file');
                }

                // Create attachment record
                $attachment = Attachment::create([
                    'attachable_type' => get_class($this->model),
                    'attachable_id' => $this->model->id,
                    'uploaded_by' => Auth::id(),
                    'filename' => $originalName,
                    'stored_filename' => $filename,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'disk' => 'public',
                ]);

                // Log activity
                $meta = [
                    'file_name' => $originalName,
                    'file_size' => $file->getSize(),
                ];
                
                if (get_class($this->model) === 'App\\Models\\Task') {
                    $meta['task_title'] = $this->model->title;
                } elseif (get_class($this->model) === 'App\\Models\\Project') {
                    $meta['project_name'] = $this->model->name;
                }
                
                LiveActivity::logActivity('file.uploaded', null, $meta);
            }

            $this->files = [];
            $this->uploading = false;
            
            $this->dispatch('toast', type: 'success', message: 'Files uploaded successfully!');
            $this->dispatch('refreshAttachments');
            
        } catch (\Exception $e) {
            $this->uploading = false;
            Log::error('File upload error: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Failed to upload files: ' . $e->getMessage());
        }
    }

    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = Attachment::findOrFail($attachmentId);
            
            // Check if user can delete this attachment
            if ($attachment->uploaded_by !== Auth::id() && !Auth::user()->currentTeam->hasUser(Auth::user())) {
                $this->dispatch('toast', type: 'error', message: 'You cannot delete this file.');
                return;
            }

            $attachment->delete();
            
            $this->dispatch('toast', type: 'success', message: 'File deleted successfully!');
            $this->dispatch('refreshAttachments');
            
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Failed to delete file: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $attachments = $this->model->attachments()->with('uploader')->get();
        
        return view('livewire.file-upload', [
            'attachments' => $attachments,
        ]);
    }
}
