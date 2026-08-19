<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' 500mg',
            'lot_number' => (string) $this->faker->numberBetween(100000, 999999),
        ];
    }
}
