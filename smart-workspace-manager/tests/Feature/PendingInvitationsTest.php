<?php

namespace Tests\Feature;

use App\Livewire\PendingInvitations;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingInvitationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pending_invitations_component_loads_for_authenticated_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
        
        $team = Team::factory()->create();
        
        TeamInvitation::create([
            'team_id' => $team->id,
            'email' => 'test@example.com',
            'role' => 'member'
        ]);

        $this->actingAs($user);

        Livewire::test(PendingInvitations::class)
            ->assertSee('Team Invitations')
            ->assertSee($team->name)
            ->assertSee('Accept');
    }

    /** @test */
    public function user_can_call_accept_invitation_method()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
        
        $team = Team::factory()->create();
        
        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => 'test@example.com',
            'role' => 'member'
        ]);

        $this->actingAs($user);

        Livewire::test(PendingInvitations::class)
            ->call('acceptInvitation', $invitation->id)
            ->assertRedirect(route('projects.index'))
            ->assertDispatched('toast', type: 'success');
            
        // Verify the invitation was processed
        $this->assertDatabaseMissing('team_invitations', ['id' => $invitation->id]);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'member'
        ]);
    }

    /** @test */
    public function no_invitations_shows_nothing()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        Livewire::test(PendingInvitations::class)
            ->assertDontSee('Team Invitations');
    }
}
