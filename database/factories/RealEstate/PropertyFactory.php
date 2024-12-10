<?php

namespace Database\Factories\RealEstate;

use App\Models\RealEstate\PropertyType;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'propertable_type' => $this->faker->randomElement(['App\\Models\\RealEstate\\Enterprise', 'App\\Models\\RealEstate\\Individual']),
            'propertable_id'   => $this->faker->randomNumber(),
            'type_id'          => PropertyType::inRandomOrder()->first()->id,
            // 'subtype_id'       => PropertySubtype::inRandomOrder()->first()->id,
            'user_id'          => User::inRandomOrder()->first()->id,
            'usage'            => $this->faker->numberBetween(1, 2),
            'code'             => $this->faker->unique()->bothify('???-#####'),
            'title'            => $this->faker->sentence,
            'slug'             => $this->faker->slug,
            'subtitle'         => $this->faker->sentence,
            'excerpt'          => $this->faker->text(200),
            'body'             => $this->faker->text(1000),
            'owner_notes'      => $this->faker->text(200),
            'url'              => $this->faker->url,
            // 'embed_videos'     => ["code" => $this->faker->numerify('???###'), "title" => $this->faker->sentence],
            'show_address'     => $this->faker->numberBetween(1, 4),
            'show_watermark'   => $this->faker->numberBetween(0, 9),
            'standard'         => $this->faker->numberBetween(1, 3),
            'tax_price'        => $this->faker->numberBetween(1000, 10000),
            'condo_price'      => $this->faker->numberBetween(500, 5000),
            'floors'           => $this->faker->numberBetween(1, 50),
            'units_per_floor'  => $this->faker->numberBetween(1, 10),
            'towers'           => $this->faker->numberBetween(1, 5),
            'construct_year'   => $this->faker->year,
            'publish_on'       => ["portal_web" => true, "portal_exho" => true, "canal_pro" => false],
            // 'tags'             => json_encode($this->faker->words(5)),
            'order'            => $this->faker->numberBetween(1, 10),
            'featured'         => $this->faker->boolean,
            'comment'          => $this->faker->boolean,
            'meta_title'       => $this->faker->sentence,
            'meta_description' => $this->faker->sentence,
            'publish_at'       => $this->faker->date('Y-m-d H:i'),
            // 'publish_at'       => $this->faker->dateTimeThisYear,
        ];
    }
}
