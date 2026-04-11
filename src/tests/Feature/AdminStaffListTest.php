<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース14：スタッフ一覧画面表示
    public function test_スタッフ一覧画面表示()
    {
        $admin = User::factory()->create(['role' => 1]);

        $staffA = User::factory()->create([
            'name' => 'スタッフA',
            'email' => 'staff_a@example.com',
            'role' => 0,
        ]);
        $staffB = User::factory()->create([
            'name' => 'スタッフB',
            'email' => 'staff_b@example.com',
            'role' => 0,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('staff_a@example.com');
        $response->assertSee('スタッフB');
        $response->assertSee('staff_b@example.com');
    }

    //テストケース14：特定のユーザーの勤怠が正しく表示される
    public function test_特定のユーザーの勤怠が正しく表示される()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '対象スタッフ']);
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-15');

        Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => '2026-04-10',
            'clock_in' => '09:12:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$staff->id}");
        $response->assertStatus(200);
        $response->assertSee('対象スタッフ');
        $response->assertSee('2026/04');
        $response->assertSee('09:12');
    }

    //テストケース14：「前月」押下時に前月の情報が表示される
    public function test_「前月」押下時に前月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create();
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-15');

        Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => '2026-03-20',
            'clock_in' => '08:45:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$staff->id}");

        $previousMonthUrl = "/admin/attendance/staff/{$staff->id}/2026/3";
        $response->assertSee($previousMonthUrl, false);

        $response = $this->get($previousMonthUrl);
        $response->assertSee('2026/03');
        $response->assertSee('08:45');
    }

    //テストケース14：「翌月」押下時に翌月の情報が表示される
    public function test_「翌月」押下時に翌月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create();
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-15');

        Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => '2026-05-10',
            'clock_in' => '09:30:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$staff->id}");

        $nextMonthUrl = "/admin/attendance/staff/{$staff->id}/2026/5";
        $response->assertSee($nextMonthUrl, false);

        $response = $this->get($nextMonthUrl);
        $response->assertSee('2026/05');
        $response->assertSee('09:30');
    }

    //テストケース14：「詳細」押下時にその日の勤怠詳細画面に遷移する
    public function test_「詳細」押下時にその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create();
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-15');

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'date' => '2026-04-10',
            'clock_in' => '09:44:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$staff->id}");

        $detailUrl = '/admin/attendance/' . $attendance->id;
        $response->assertSee($detailUrl, false);
        $response = $this->get($detailUrl);

        $response->assertSee('09:44');
    }
}
