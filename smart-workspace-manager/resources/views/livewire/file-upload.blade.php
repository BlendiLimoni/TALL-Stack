<div class="space-y-4">
    <!-- File Upload Area -->
    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
        <input 
            type="file" 
            wire:model="files" 
            multiple 
            class="hidden" 
            id="file-upload-{{ $model->id }}"
            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip"
        >
        
        <label 
            for="file-upload-{{ $model->id }}" 
            class="cursor-pointer inline-flex flex-col items-center"
        >
            @if($uploading)
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-3"></div>
                <p class="text-gray-600 dark:text-gray-400">Uploading files...</p>
            @else
                <svg class="w-8 h-8 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span>
                    or drag and drop files here
                </p>
                <p class="text-xs text-gray-500 mt-1">Maximum file size: 10MB</p>
            @endif
        </label>
    </div>

    <!-- Attachments List -->
    @if($attachments->count() > 0)
        <div class="space-y-2">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Attachments ({{ $attachments->count() }})</h4>
            
            @foreach($attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <!-- File Icon -->
                        <div class="flex-shrink-0">
                            @if($attachment->is_image)
                                <img 
                                    src="{{ $attachment->url }}" 
                                    alt="{{ $attachment->filename }}"
                                    class="w-10 h-10 object-cover rounded"
                                >
                            @else
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- File Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $attachment->filename }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $attachment->formatted_size }} • 
                                Uploaded by {{ $attachment->uploader->name }} • 
                                {{ $attachment->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center space-x-2">
                        @if($attachment->is_image)
                            <button 
                                type="button"
                                @click="$dispatch('show-image-modal', { url: '{{ $attachment->url }}', filename: '{{ $attachment->filename }}' })"
                                class="text-blue-600 hover:text-blue-700 text-xs font-medium"
                            >
                                Preview
                            </button>
                        @endif
                        
                        <a 
                            href="{{ $attachment->url }}" 
                            download="{{ $attachment->filename }}"
                            class="text-blue-600 hover:text-blue-700 text-xs font-medium"
                        >
                            Download
                        </a>
                        
                        @if($attachment->uploaded_by === auth()->id())
                            <button 
                                type="button"
                                wire:click="deleteAttachment({{ $attachment->id }})"
                                wire:confirm="Are you sure you want to delete this file?"
                                class="text-red-600 hover:text-red-700 text-xs font-medium"
                            >
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Loading indicator for file selection -->
    <div wire:loading wire:target="files" class="text-center py-4">
        <div class="inline-flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Processing files...</span>
        </div>
    </div>
</div>
