<?php

namespace App\Console\Commands;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Console\Command;

class AcceptInvitation extends Command
{
    protected $signature = 'team:accept-invitation {email} {invitation-id}';
    protected $description = 'Manually accept a team invitation for testing';

    public function handle()
    {
        $email = $this->argument('email');
        $invitationId = $this->argument('invitation-id');
        
        $invitation = TeamInvitation::findOrFail($invitationId);
        
        if ($invitation->email !== $email) {
            $this->error('Invitation email does not match.');
            return;
        }
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found.');
            return;
        }
        
        // Add user to team
        $invitation->team->users()->attach($user->id, ['role' => $invitation->role]);
        
        // Set as current team if user doesn't have one
        if (!$user->current_team_id) {
            $user->forceFill(['current_team_id' => $invitation->team->id])->save();
        }
        
        // Delete the invitation
        $invitation->delete();
        
        $this->info("User {$email} has been added to team {$invitation->team->name} as {$invitation->role}");
    }
}
