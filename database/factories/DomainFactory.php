<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'framework' => $this->faker->randomElement(['ISO27001', 'NIST', 'CIS', 'SOC2']),
            'description' => $this->faker->sentence(),
        ];
    }
}
