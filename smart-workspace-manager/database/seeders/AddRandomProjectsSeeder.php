<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Team;
use App\Models\Project;

class AddRandomProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Ensure the user has a team
            $team = $user->currentTeam;
            if (!$team) {
                $team = Team::whereHas('users', fn($q) => $q->where('users.id', $user->id))->first();
            }
            if (!$team) {
                $team = Team::create([
                    'user_id' => $user->id,
                    'name' => $user->name . "'s Team",
                    'personal_team' => true,
                ]);
                $team->users()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
            }
            if ($user->current_team_id !== $team->id) {
                DB::table('users')->where('id', $user->id)->update(['current_team_id' => $team->id]);
            }

            // Build random project data
            $name = 'Random Project ' . Str::upper(Str::random(5));
            $desc = 'Auto-seeded project for ' . $user->name;
            $color = '#' . substr(md5($user->id . '|' . $user->email), 0, 6);

            // Insert directly, but avoid Scout indexing side-effects
            Project::withoutSyncingToSearch(function () use ($team, $user, $name, $desc, $color) {
                Project::create([
                    'team_id' => $team->id,
                    'created_by' => $user->id,
                    'name' => $name,
                    'description' => $desc,
                    'color' => $color,
                ]);
            });
        }
    }
}
