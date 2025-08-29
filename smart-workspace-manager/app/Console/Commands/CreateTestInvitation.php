<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Console\Command;

class CreateTestInvitation extends Command
{
    protected $signature = 'team:create-test-invitation {email=test@example.com} {role=member}';
    protected $description = 'Create a test invitation for testing purposes';

    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->argument('role');
        
        $team = Team::first();
        
        if (!$team) {
            $this->error('No team found');
            return;
        }
        
        // Check if invitation already exists
        $existing = TeamInvitation::where('email', $email)->where('team_id', $team->id)->first();
        if ($existing) {
            $this->info("Invitation already exists for {$email}");
            return;
        }
        
        TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => $role,
        ]);
        
        $this->info("Test invitation created for {$email} to join {$team->name} as {$role}");
    }
}
