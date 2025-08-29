<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Mail\TeamInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class TeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->team->users()->attach($this->user->id, ['role' => 'admin']);
        $this->user->forceFill(['current_team_id' => $this->team->id])->save();
        
        // Make sure the user owns the team for team management permissions
        $this->team->update(['user_id' => $this->user->id]);
    }

    public function test_team_invitations_can_be_sent()
    {
        Mail::fake();

        $this->actingAs($this->user);

        $component = Livewire::test('teams.team-member-manager', ['team' => $this->team])
            ->set('addTeamMemberForm', [
                'email' => 'test@invited.com',
                'role' => 'member',
            ])
            ->call('addTeamMember');

        // Check that invitation was created in database
        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $this->team->id,
            'email' => 'test@invited.com',
            'role' => 'member',
        ]);

        // Check that email was sent
        Mail::assertSent(TeamInvitation::class);
    }

    public function test_team_invitations_can_be_cancelled()
    {
        $this->actingAs($this->user);

        // Create an invitation
        $invitation = $this->team->teamInvitations()->create([
            'email' => 'test@invited.com',
            'role' => 'member',
        ]);

        $component = Livewire::test('teams.team-member-manager', ['team' => $this->team])
            ->call('cancelTeamInvitation', $invitation->id);

        // Check that invitation was removed from database
        $this->assertDatabaseMissing('team_invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_team_settings_page_is_accessible()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('teams.show', $this->team));

        $response->assertStatus(200);
        $response->assertSee('Team Settings');
        $response->assertSee('Add Team Member');
    }

    public function test_invited_user_sees_pending_invitations()
    {
        // Create an invited user
        $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
        
        // Create an invitation for this user
        $invitation = $this->team->teamInvitations()->create([
            'email' => 'invited@example.com',
            'role' => 'member',
        ]);

        // Login as the invited user
        $this->actingAs($invitedUser);

        // Visit the projects page where pending invitations are shown
        $response = $this->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Team Invitations');
        $response->assertSee($this->team->name);
        $response->assertSee('Accept');
    }
}
