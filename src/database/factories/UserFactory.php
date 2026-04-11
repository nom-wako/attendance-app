<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function clockedIn()
    {
        return $this->afterCreating(function (User $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today(),
                'clock_in' => Carbon::now()->subHours(2),
            ]);
        });
    }

    public function onBreak()
    {
        return $this->afterCreating(function (User $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today(),
                'clock_in' => Carbon::now()->subHours(4),
            ]);

            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now()->subMinutes(30),
            ]);
        });
    }

    public function clockedOut()
    {
        return $this->afterCreating(function (User $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today(),
                'clock_in' => Carbon::now()->subHours(9),
                'clock_out' => Carbon::now(),
            ]);
        });
    }
}
