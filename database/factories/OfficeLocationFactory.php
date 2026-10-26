<?php

namespace Database\Factories;

use App\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Kantor '.fake()->city(),
            'address' => fake()->address(),
            'polygon_coordinates' => [
                ['lat' => -6.175110, 'lng' => 106.826500],
                ['lat' => -6.175110, 'lng' => 106.827800],
                ['lat' => -6.176200, 'lng' => 106.827800],
                ['lat' => -6.176200, 'lng' => 106.826500],
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the office location is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
