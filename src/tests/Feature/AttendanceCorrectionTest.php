<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    private function createAttendanceWithRest($user)
    {
        $attendance =  Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
        return $attendance;
    }

    //テストケース11：出勤時間が退勤時間より後になっているバリデーションチェック
    public function test_出勤時間が退勤時間より後になっているバリデーションチェック()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendanceWithRest($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    //テストケース11：休憩開始時間が退勤時間より後になっているバリデーションチェック
    public function test_休憩開始時間が退勤時間より後になっているバリデーションチェック()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendanceWithRest($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                [
                    'start_time' => '19:00',
                    'end_time' => '19:30',
                ]
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['rests.0.start_time' => '休憩時間が不適切な値です']);
    }

    //テストケース11：休憩終了時間が退勤時間より後になっているバリデーションチェック
    public function test_休憩終了時間が退勤時間より後になっているバリデーションチェック()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendanceWithRest($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                [
                    'start_time' => '17:00',
                    'end_time' => '19:00',
                ]
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['rests.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    //テストケース11：備考欄が未入力のバリデーションチェック
    public function test_備考欄が未入力のバリデーションチェック()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendanceWithRest($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '',
        ]);

        $response->assertInvalid(['remarks' => '備考を記入してください']);
    }

    //テストケース11：修正申請から申請一覧
    public function test_修正申請から申請一覧()
    {
        $user = User::factory()->create(['role' => 0]);
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:30',
            'rests' => [
                $rest->id => [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
            'remarks' => 'テスト申請',
        ]);

        $response->assertRedirect(route('attendance.show', $attendance->id));

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'remarks' => 'テスト申請',
            'status' => 1,
        ]);

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)->first();

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee('テスト申請');
        $response->assertSee("/attendance/detail/{$correction->id}", false);
        $response = $this->get("/attendance/detail/{$correction->id}");
        $response->assertSee('承認待ちのため修正はできません。');

        $this->actingAs($admin);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('テスト申請');
        $response = $this->post("/stamp_correction_request/approve/{$correction->id}");
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 2,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => '2026-04-10 18:30:00',
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('テスト申請');
    }
}
