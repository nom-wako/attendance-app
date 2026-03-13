<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ];
    }
}
