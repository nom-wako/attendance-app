<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース9：自分が行った勤怠情報の全件表示
    public function test_自分が行った勤怠情報の全件表示()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:11:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => '2026-04-11',
            'clock_in' => '10:22:00',
        ]);

        $this->actingAs($user);
        Carbon::setTestNow('2026-04-15');
        $response = $this->get('/attendance/list');

        $response->assertSee('09:11');
        $response->assertDontSee('10:22');
    }

    //テストケース9：勤怠一覧画面遷移時現在の月が表示される
    public function test_勤怠一覧画面遷移時現在の月が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2026-04-15');
        $response = $this->get('/attendance/list');

        $response->assertSee('2026/04');
    }

    //テストケース9：「前月」押下時に前月の情報が表示される
    public function test_「前月」押下時に前月の情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2026-04-15');
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-03-20',
            'clock_in' => '08:33:00',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('/attendance/list/2026/3', false);
        $response = $this->get('/attendance/list/2026/3');

        $response->assertSee('2026/03');
        $response->assertSee('08:33');
    }

    //テストケース9：「翌月」押下時に翌月の情報が表示される
    public function test_「翌月」押下時に翌月の情報が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2026-04-15');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:44:00',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('/attendance/list/2026/5', false);
        $response = $this->get('/attendance/list/2026/5');

        $response->assertSee('2026/05');
        $response->assertSee('09:44');
    }

    //テストケース9：「詳細」押下時にその日の勤怠詳細画面に遷移する
    public function test_「詳細」押下時にその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:44:00',
        ]);

        $this->actingAs($user);
        Carbon::setTestNow('2026-04-15');
        $response = $this->get('/attendance/list');

        $detailUrl = '/attendance/detail/' . $attendance->id;
        $response->assertSee($detailUrl, false);
        $response = $this->get($detailUrl);

        $response->assertSee('09:44');
    }

    //テストケース10：勤怠詳細情報取得機能
    public function test_勤怠詳細情報取得機能()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

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

        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee('テスト太郎');

        $response->assertSee('2026年');
        $response->assertSee('4月10日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
