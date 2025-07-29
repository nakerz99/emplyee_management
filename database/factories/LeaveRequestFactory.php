<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 14));
        $daysRequested = Carbon::parse($startDate)->diffInDays($endDate) + 1;

        return [
            'user_id' => User::factory(),
            'leave_type' => $this->faker->randomElement(['vacation', 'sick', 'personal', 'other']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_requested' => $daysRequested,
            'reason' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
            'approved_at' => null,
            'admin_notes' => null,
        ];
    }

    /**
     * Indicate that the leave request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'admin_notes' => null,
        ]);
    }

    /**
     * Indicate that the leave request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'admin_notes' => $this->faker->optional(0.7)->sentence(),
        ]);
    }

    /**
     * Indicate that the leave request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'admin_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the leave request is for vacation.
     */
    public function vacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => 'vacation',
        ]);
    }

    /**
     * Indicate that the leave request is for sick leave.
     */
    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => 'sick',
        ]);
    }

    /**
     * Indicate that the leave request is for personal leave.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => 'personal',
        ]);
    }
}
