<?php

namespace Database\Factories\Polymorphics;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'addressable_id'   => null, // Set dynamically
            'addressable_type' => null, // Set dynamically
            'name'             => $this->faker->randomElement(['Casa', 'Trabalho', 'Outros']),
            'is_main'          => $this->faker->boolean,
            'zipcode'          => $this->faker->postcode,
            'state'            => $this->faker->state,
            'uf'               => $this->faker->randomElement([
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE',
                'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
            ]),
            'city'             => $this->faker->city,
            'country'          => $this->faker->country,
            'district'         => $this->faker->citySuffix,
            'address_line'     => $this->faker->streetAddress,
            'number'           => $this->faker->buildingNumber,
            'complement'       => $this->faker->secondaryAddress,
            'custom_street'    => $this->faker->streetName,
            'custom_block'     => $this->faker->numerify('Qd. ###'),
            'custom_lot'       => $this->faker->numerify('Lt. ##'),
            'reference'        => $this->faker->sentence,
            'gmap_coordinates' => $this->faker->latitude . ',' . $this->faker->longitude,
        ];
    }
}
