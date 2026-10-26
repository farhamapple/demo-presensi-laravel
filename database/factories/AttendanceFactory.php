<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'office_location_id' => OfficeLocation::factory(),
            'date' => now()->toDateString(),
            'check_in_at' => now()->setTime(8, fake()->numberBetween(0, 30), 0),
            'check_out_at' => now()->setTime(17, fake()->numberBetween(0, 30), 0),
            'check_in_latitude' => -6.175500,
            'check_in_longitude' => 106.827000,
            'check_out_latitude' => -6.175500,
            'check_out_longitude' => 106.827000,
            'status' => AttendanceStatus::Present,
            'notes' => fake()->optional()->sentence(),
            'device_name' => fake()->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S23', 'Xiaomi 13T', 'Pixel 8']),
            'device_id' => fake()->uuid(),
            'check_out_device_name' => null,
        ];
    }

    /**
     * Indicate that the attendance is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => now()->setTime(9, 15, 0),
            'status' => AttendanceStatus::Late,
        ]);
    }

    /**
     * Indicate that the attendance is outside the geofence area.
     */
    public function outsideArea(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_latitude' => -6.300000,
            'check_in_longitude' => 106.900000,
            'status' => AttendanceStatus::OutsideArea,
        ]);
    }
}
