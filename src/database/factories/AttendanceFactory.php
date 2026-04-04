<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Attendance::class;

    public function definition(): array
    {
        $clockIn = $this->faker->dateTimeBetween('09:00:00', '10:00:00');
        $clockOut = $this->faker->dateTimeBetween('18:00:00', '21:00:00');
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
        ];
    }
}
