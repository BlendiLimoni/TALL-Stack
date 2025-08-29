<?php

namespace App\Livewire;

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PendingInvitations extends Component
{
    public $invitations = [];

    public function mount()
    {
        if (Auth::check()) {
            $this->invitations = TeamInvitation::where('email', Auth::user()->email)
                ->with('team')
                ->get();
        }
    }

    public function acceptInvitation($invitationId)
    {
        Log::info('PendingInvitations: acceptInvitation called', ['invitation_id' => $invitationId]);
        
        $invitation = TeamInvitation::findOrFail($invitationId);
        
        if ($invitation->email !== Auth::user()->email) {
            Log::warning('PendingInvitations: Email mismatch', [
                'invitation_email' => $invitation->email,
                'user_email' => Auth::user()->email
            ]);
            $this->dispatch('toast', type: 'error', message: 'This invitation is not for you.');
            return;
        }

        $user = Auth::user();
        $team = $invitation->team;

        Log::info('PendingInvitations: Processing invitation', [
            'user_id' => $user->id,
            'team_id' => $team->id,
            'role' => $invitation->role
        ]);

        try {
            // Add user to team
            $team->users()->syncWithoutDetaching([$user->id => ['role' => $invitation->role]]);
            Log::info('PendingInvitations: User added to team');
            
            // Set as current team if user doesn't have one
            if (!$user->current_team_id) {
                $user->current_team_id = $team->id;
                $user->save();
                
                // Refresh the user instance
                $user->refresh();
                Log::info('PendingInvitations: Set as current team');
            }
            
            // Delete the invitation
            $invitation->delete();
            Log::info('PendingInvitations: Invitation deleted');
            
            // Show success message
            $this->dispatch('toast', type: 'success', message: "You've successfully joined {$team->name}!");
            
            // Refresh invitations list
            $this->mount();
            
            // Optionally redirect to refresh the page with the new team context
            return $this->redirect(route('projects.index'));
            
        } catch (\Exception $e) {
            Log::error('PendingInvitations: Exception during acceptance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('toast', type: 'error', message: 'Failed to accept invitation. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.pending-invitations');
    }
}
