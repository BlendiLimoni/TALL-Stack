<div>
    <h2 class="text-lg font-semibold mb-4">{{ $project ? 'Edit Project' : 'New Project' }}</h2>
    <div class="space-y-4">
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
    </div>
    <div class="mt-6 flex justify-end gap-3">
        <x-secondary-button x-on:click="$dispatch('close-project-modal')">Cancel</x-secondary-button>
        <x-button wire:click="save">Save</x-button>
    </div>
</div>
