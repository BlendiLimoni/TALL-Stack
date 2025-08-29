<div wire:id="pending-invitations-{{ auth()->id() }}">
@if($invitations->count() > 0)
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-3">
            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            Team Invitations
        </h3>
        
        @foreach($invitations as $invitation)
            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-3 mb-2 border border-gray-200 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                        You've been invited to join <strong>{{ $invitation->team->name }}</strong>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Role: {{ ucfirst($invitation->role) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="acceptInvitation({{ $invitation->id }})" 
                            wire:target="acceptInvitation"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm rounded-md transition">
                        <span wire:loading.remove wire:target="acceptInvitation">Accept</span>
                        <span wire:loading wire:target="acceptInvitation">Processing...</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
</div>
