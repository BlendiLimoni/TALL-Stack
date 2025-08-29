<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->project->team->hasUser($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->team->hasUser($user);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->team->hasUser($user);
    }

    public function delete(User $user, Task $task): bool
    {
        // Only team owners or admins can delete; for demo allow owners/admins
        $team = $task->project->team;
        if ($team->owner->is($user)) {
            return true;
        }
        // Jetstream stores roles via membership pivot; check role
        $membership = $team->users()->where('users.id', $user->id)->first()?->membership;
        return in_array($membership?->role, ['admin']);
    }
}
