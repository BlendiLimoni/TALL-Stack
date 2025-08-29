<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'done']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'assigned_user_id' => fake()->optional()->randomElement([null, User::factory()]),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
