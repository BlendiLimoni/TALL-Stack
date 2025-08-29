<div>
    <h2 class="text-lg font-semibold mb-4">{{ $project ? 'Edit Project' : 'New Project' }}</h2>
    <form wire:submit.prevent="save" class="space-y-4" x-on:submit="console.debug('[form:submit] triggered')">
        <div>
            <x-label for="name" value="Name" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="name" />
            <x-input-error for="name" class="mt-1" />
        </div>
        <div>
            <x-label for="description" value="Description" />
            <textarea id="description" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-900" rows="4" wire:model.defer="description"></textarea>
            <x-input-error for="description" class="mt-1" />
        </div>
        <div>
            <x-label for="color" value="Color (hex)" />
            <x-input id="color" type="text" class="mt-1 block w-40" placeholder="#6366f1" wire:model.defer="color" />
            <x-input-error for="color" class="mt-1" />
        </div>
        <div class="mt-6 flex justify-between">
            <div>
                @if($project)
                    @can('delete', $project)
                    <x-danger-button type="button" 
                        wire:click="delete" 
                        wire:loading.attr="disabled" 
                        wire:target="delete"
                        x-on:click="console.debug('[delete:click] button clicked')"
                        onclick="return confirm('Are you sure you want to delete this project? This action cannot be undone.')">
                        Delete Project
                    </x-danger-button>
                    @endcan
                @endif
            </div>
            <div class="flex gap-3">
                <x-secondary-button type="button" x-on:click="console.debug('[cancel:click]'); window.dispatchEvent(new CustomEvent('close-project-modal'))">Cancel</x-secondary-button>
                <x-button type="submit" wire:loading.attr="disabled" wire:target="save" wire:click="save" x-on:click="console.debug('[save:click] button clicked')">Save</x-button>
            </div>
        </div>
    </form>
</div>
