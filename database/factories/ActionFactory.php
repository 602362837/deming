<?php

namespace Database\Factories;

use App\Models\Action;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionFactory extends Factory
{
    protected $model = Action::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'reference' => $this->faker->bothify('REF-####'),
            'type' => $this->faker->randomElement(['1', '2', '3', '4']),
            'criticity' => 0,
            'status' => 0,
            'scope' => $this->faker->word(),
            'cause' => $this->faker->sentence(),
            'remediation' => $this->faker->sentence(),
            'progress' => $this->faker->numberBetween(0, 100),
            'creation_date' => now()->format('Y-m-d'),
            'due_date' => now()->addMonths(3)->format('Y-m-d'),
        ];
    }

    public function closed(): static
    {
        return $this->state([
            'status' => 1,
            'close_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
    }
}
