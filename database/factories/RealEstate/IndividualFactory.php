<?php

namespace Database\Factories\RealEstate;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RealEstate\Individual>
 */
class IndividualFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role'        => $this->faker->numberBetween(1, 3),
            'sale_price'  => $this->faker->numberBetween(100000, 500000),
            'rent_price'  => $this->faker->numberBetween(1000, 5000),
            'rent_period' => $this->faker->numberBetween(1, 4),
            'useful_area' => $this->faker->numberBetween(50, 200),
            'total_area'  => $this->faker->numberBetween(100, 300),
            'bedroom'     => $this->faker->numberBetween(1, 5),
            'suite'       => $this->faker->numberBetween(0, 3),
            'bathroom'    => $this->faker->numberBetween(1, 3),
            'garage'      => $this->faker->numberBetween(0, 2),
        ];
    }
}
