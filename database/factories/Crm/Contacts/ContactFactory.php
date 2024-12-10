<?php

namespace Database\Factories\Crm\Contacts;

use App\Models\Crm\Contacts\Source;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Crm\Contacts\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contactable_type' => $this->faker->randomElement(['App\\Models\\Crm\\Contacts\\Individual', 'App\\Models\\Crm\\Contacts\\LegalEntity']),
            'contactable_id'   => $this->faker->randomNumber(),
            'user_id'          => User::inRandomOrder()->first()->id,
            'source_id'        => Source::inRandomOrder()->first()->id,
            'name'             => $this->faker->name,
            'emails'           => [["email" => $this->faker->unique()->safeEmail(), "name"  => $this->faker->randomElement(['Pessoal', 'Trabalho', 'Outros'])]],
            'phones'           => [["number" => $this->faker->numerify('(##) #####-####'), "name" => $this->faker->randomElement(['Celular', 'Whatsapp', 'Casa', 'Trabalho', 'Outros'])]],
            'status'           => $this->faker->boolean,
        ];
    }
}
