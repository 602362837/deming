<?php

namespace Database\Factories;

use App\Models\Risk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiskFactory extends Factory
{
    protected $model = Risk::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'owner_id' => null,
            'probability' => $this->faker->numberBetween(1, 5),
            'probability_comment' => null,
            'impact' => $this->faker->numberBetween(1, 5),
            'impact_comment' => null,
            'exposure' => null,
            'vulnerability' => null,
            'status' => Risk::STATUS_NOT_EVALUATED,
            'status_comment' => null,
            'review_frequency' => 12,
            'next_review_at' => now()->addYear()->format('Y-m-d'),
        ];
    }
}
