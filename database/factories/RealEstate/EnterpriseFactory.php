<?php

namespace Database\Factories\RealEstate;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RealEstate\Enterprise>
 */
class EnterpriseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role'            => $this->faker->numberBetween(1, 3),
            'min_price'       => $this->faker->numberBetween(100000, 500000),
            'max_price'       => $this->faker->numberBetween(500000, 1000000),
            'min_useful_area' => $this->faker->numberBetween(50, 100),
            'max_useful_area' => $this->faker->numberBetween(100, 200),
            'min_total_area'  => $this->faker->numberBetween(100, 200),
            'max_total_area'  => $this->faker->numberBetween(200, 300),
            'min_bedroom'     => $this->faker->numberBetween(1, 3),
            'max_bedroom'     => $this->faker->numberBetween(3, 5),
            'min_suite'       => $this->faker->numberBetween(1, 2),
            'max_suite'       => $this->faker->numberBetween(2, 3),
            'min_bathroom'    => $this->faker->numberBetween(1, 2),
            'max_bathroom'    => $this->faker->numberBetween(2, 3),
            'min_garage'      => $this->faker->numberBetween(1, 2),
            'max_garage'      => $this->faker->numberBetween(2, 3),
            // 'construction_follow_up' => null,
        ];
    }
}
