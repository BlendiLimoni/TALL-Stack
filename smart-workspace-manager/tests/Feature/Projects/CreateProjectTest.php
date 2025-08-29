<?php

namespace Tests\Feature\Projects;

use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\ProjectForm;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_via_livewire_form(): void
    {
    /** @var User $user */
    $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Test Team']);
        $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
        $user->forceFill(['current_team_id' => $team->id])->save();

    $this->actingAs($user, 'web');

        Livewire::test(ProjectForm::class)
            ->set('name', 'My New Project')
            ->set('description', 'Desc')
            ->set('color', '#123456')
            ->call('save')
            ->assertRedirect('/projects');

        $this->assertDatabaseHas('projects', [
            'team_id' => $team->id,
            'name' => 'My New Project',
            'description' => 'Desc',
            'color' => '#123456',
            'created_by' => $user->id,
        ]);
    }

    public function test_projects_index_lists_created_project(): void
    {
    /** @var User $user */
    $user = User::factory()->create(['email_verified_at' => now()]);
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'List Team']);
        $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
        $user->forceFill(['current_team_id' => $team->id])->save();

        $project = Project::create([
            'team_id' => $team->id,
            'name' => 'Visible Project',
            'description' => 'Should appear on index',
            'created_by' => $user->id,
        ]);

    $this->actingAs($user, 'web');

        Livewire::test(ProjectsIndex::class)
            ->assertSee('Visible Project')
            ->assertSee('Should appear on index');
    }
}
