<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pack>
 */
class PackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->paragraph,
            'time_start' => $this->faker->dateTimeBetween('now', '+30 days'),
            'time_end' => $this->faker->dateTimeBetween('now', '+30 days'),
            'stock' => $this->faker->numberBetween(1, 100),
            'user_id' => function () {
                return User::factory()->create()->id; 
            },
            'photo_url' => 'https://res.cloudinary.com/dtvngobyy/image/upload/v1694470684/hmeycscun0jezhdrg0fq.png',
        ];
    }
}
