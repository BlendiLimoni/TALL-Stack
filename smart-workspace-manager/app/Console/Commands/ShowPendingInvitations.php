<?php

namespace App\Console\Commands;

use App\Models\TeamInvitation;
use Illuminate\Console\Command;

class ShowPendingInvitations extends Command
{
    protected $signature = 'team:show-invitations';
    protected $description = 'Show pending team invitations and their acceptance links';

    public function handle()
    {
        $invitations = TeamInvitation::with('team')->get();

        if ($invitations->isEmpty()) {
            $this->info('No pending invitations found.');
            return;
        }

        $this->info('Pending Team Invitations:');
        $this->newLine();

        foreach ($invitations as $invitation) {
            $this->line("Email: {$invitation->email}");
            $this->line("Team: {$invitation->team->name}");
            $this->line("Role: {$invitation->role}");
            $this->line("Acceptance Link: " . url("/team-invitations/{$invitation->id}"));
            $this->newLine();
        }

        $this->comment('Copy the acceptance link and visit it while logged in as the invited user.');
    }
}
