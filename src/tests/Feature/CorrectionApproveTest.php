<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionApproveTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース15：管理者の申請一覧表示
    public function test_管理者の申請一覧表示()
    {
        $admin = User::factory()->create(['role' => 1]);
        $userA = User::factory()->create(['name' => 'ユーザーA']);
        $userB = User::factory()->create(['name' => 'ユーザーB']);

        $attendanceA = Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
        ]);
        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendanceA->id,
            'clock_in' => '08:30:00',
            'remarks' => 'ユーザーAの申請',
            'status' => 1,
        ]);

        $attendanceB = Attendance::factory()->create([
            'user_id' => $userB->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
        ]);
        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendanceB->id,
            'clock_in' => '08:30:00',
            'remarks' => 'ユーザーBの申請',
            'status' => 2,
        ]);

        $this->actingAs($admin);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee('ユーザーAの申請');
        $response->assertSee('ユーザーBの申請');
    }

    //テストケース15：申請詳細の表示
    public function test_申請詳細の表示()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'remarks' => 'テスト備考',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get("/stamp_correction_request/approve/{$correction->id}");

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('テスト備考');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }

    //テストケース15：申請の承認処理の実行
    public function test_申請の承認処理の実行()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '2026-04-10 09:00:00',
            'clock_out' => '2026-04-10 18:00:00',
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '2026-04-10 08:45:00',
            'clock_out' => '2026-04-10 18:15:00',
            'remarks' => 'テスト備考',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->post("/stamp_correction_request/approve/{$correction->id}");

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 2,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2026-04-10 08:45:00',
            'clock_out' => '2026-04-10 18:15:00',
        ]);
    }
}
