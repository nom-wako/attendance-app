<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        $user = User::create([
            'name' => '一般テスト',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 0,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $targetDate = now()->subDays($i)->format('Y-m-d');
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $targetDate,
            ]);
            Rest::factory()->create([
                'attendance_id' => $attendance->id,
            ]);
        }
    }
}
