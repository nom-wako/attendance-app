<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース12：管理者ユーザーの勤怠情報一覧表示
    public function test_管理者ユーザーの勤怠情報一覧表示()
    {
        $admin = User::factory()->create(['role' => 1]);
        $userA = User::factory()->create(['name' => 'テストユーザーA']);
        $userB = User::factory()->create(['name' => 'テストユーザーB']);

        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-10');

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => '2026-04-10',
            'clock_in' => '09:00:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $userB->id,
            'date' => '2026-04-10',
            'clock_in' => '09:30:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => '2026-04-09',
            'clock_in' => '08:00:00',
        ]);

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('2026/04/10');
        $response->assertSee('テストユーザーA');
        $response->assertSee('09:00');
        $response->assertSee('テストユーザーB');
        $response->assertSee('09:30');
        $response->assertDontSee('08:00');
    }

    //テストケース12：「前日」押下時に前日の勤怠情報が表示される
    public function test_「前日」押下時に前日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);
        $userA = User::factory()->create(['name' => 'テストユーザーA']);
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-10');

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => '2026-04-09',
            'clock_in' => '08:55:00',
        ]);

        $response = $this->get('/admin/attendance/list');

        $previousDayUrl = '/admin/attendance/list/2026-04-09';
        $response->assertSee($previousDayUrl, false);

        $response = $this->get($previousDayUrl);

        $response->assertSee('08:55');
    }

    //テストケース12：「翌日」押下時に翌日の勤怠情報が表示される
    public function test_「翌日」押下時に翌日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);
        $userA = User::factory()->create(['name' => 'テストユーザーA']);
        $this->actingAs($admin);

        Carbon::setTestNow('2026-04-10');

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'date' => '2026-04-11',
            'clock_in' => '09:15:00',
        ]);

        $response = $this->get('/admin/attendance/list');

        $nextDayUrl = '/admin/attendance/list/2026-04-11';
        $response->assertSee($nextDayUrl, false);

        $response = $this->get($nextDayUrl);

        $response->assertSee('09:15');
    }

    //テストケース13：勤怠詳細画面表示
    public function test_勤怠詳細画面表示()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['name' => '対象ユーザー']);

        $attendance = Attendance::factory()->create([
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

        $this->actingAs($admin);

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('対象ユーザー');
        $response->assertSee('2026年');
        $response->assertSee('4月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    private function performAdminUpdate($admin, $attendance, $data)
    {
        $this->actingAs($admin);
        return $this->post("/admin/attendance/{$attendance->id}", $data);
    }

    //テストケース13：出勤時間が退勤時間より後になっているバリデーションチェック
    public function test_出勤時間が退勤時間より後になっているバリデーションチェック()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['date' => '2026-04-10']);

        $response = $this->performAdminUpdate($admin, $attendance, [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    //テストケース13：休憩開始時間が退勤時間より後になっているバリデーションチェック
    public function test_休憩開始時間が退勤時間より後になっているバリデーションチェック()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['date' => '2026-04-10', 'clock_out' => '18:00:00',]);
        $rest = Rest::factory()->create(['attendance_id' => $attendance->id,]);

        $response = $this->performAdminUpdate($admin, $attendance, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                [
                    'id' => $rest->id,
                    'start_time' => '19:00',
                    'end_time' => '19:30',
                ]
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['rests.0.start_time' => '休憩時間が不適切な値です']);
    }

    //テストケース13：休憩終了時間が退勤時間より後になっているバリデーションチェック
    public function test_休憩終了時間が退勤時間より後になっているバリデーションチェック()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['date' => '2026-04-10', 'clock_out' => '18:00:00']);
        $rest = Rest::factory()->create(['attendance_id' => $attendance->id]);

        $response = $this->performAdminUpdate($admin, $attendance, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                [
                    'id' => $rest->id,
                    'start_time' => '17:00',
                    'end_time' => '19:00',
                ]
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertInvalid(['rests.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    //テストケース13：備考欄が未入力のバリデーションチェック
    public function test_備考欄が未入力のバリデーションチェック()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['date' => '2026-04-10']);

        $response = $this->performAdminUpdate($admin, $attendance, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '',
        ]);

        $response->assertInvalid(['remarks' => '備考を記入してください']);
    }
}
