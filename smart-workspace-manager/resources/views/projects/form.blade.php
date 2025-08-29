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
        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="console.debug('[cancel:click]'); window.dispatchEvent(new CustomEvent('close-project-modal'))">Cancel</x-secondary-button>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="save" wire:click="save" x-on:click="console.debug('[save:click] button clicked')">Save</x-button>
        </div>
    </form>
</div>
