<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TimeRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeRecord>
 */
class TimeRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-3 months', 'now');
        $clockInHour = $this->faker->numberBetween(8, 10);
        $clockInMinute = $this->faker->numberBetween(0, 59);
        $clockIn = Carbon::parse($date)->setTime($clockInHour, $clockInMinute);
        
        $workHours = $this->faker->randomFloat(1, 6, 10);
        $clockOut = $clockIn->copy()->addHours($workHours);
        
        $overtimeHours = max(0, $workHours - 8);
        $breakHours = $this->faker->randomFloat(1, 0, 1);

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_hours' => $workHours,
            'break_hours' => $breakHours,
            'overtime_hours' => $overtimeHours,
            'status' => $this->faker->randomElement(['active', 'completed', 'absent']),
            'notes' => $this->faker->optional(0.1)->sentence(),
        ];
    }

    /**
     * Indicate that the time record is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Indicate that the time record has overtime.
     */
    public function withOvertime(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_hours' => $this->faker->randomFloat(1, 9, 12),
            'overtime_hours' => $this->faker->randomFloat(1, 1, 4),
        ]);
    }

    /**
     * Indicate that the time record is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
