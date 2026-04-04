<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        User::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        $testUser = User::create([
            'name' => '一般テスト',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $randomUsers = User::factory(5)->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $generalUsers = collect([$testUser])->concat($randomUsers);

        foreach ($generalUsers as $user) {
            $startDate = Carbon::now()->subDays(30);
            for ($i = 0; $i <= 30; $i++) {
                $date = $startDate->copy()->addDays($i);

                if (rand(1, 100) > 80) {
                    continue;
                }

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                ]);

                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);
            }
        }
    }
}
