<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース4：日時取得機能
    public function test_現在の日時情報が正しく出力されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::now();;
        Carbon::setTestNow($now);

        $response = $this->get('/attendance');

        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $expectedDayOfWeek = $week[$now->dayOfWeek];
        $expectedDate = $now->format('Y年n月j日') . '(' . $expectedDayOfWeek . ')';

        $expectedTime = $now->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    //テストケース5：勤務外の勤怠ステータス表示
    public function test_勤務外の勤怠ステータス表示()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    //テストケース5：出勤中の勤怠ステータス表示
    public function test_出勤中の勤怠ステータス表示()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    //テストケース5：休憩中の勤怠ステータス表示
    public function test_休憩中の勤怠ステータス表示()
    {
        $user = User::factory()->onBreak()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    //テストケース5：退勤済の勤怠ステータス表示
    public function test_退勤済の勤怠ステータス表示()
    {
        $user = User::factory()->clockedOut()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    //テストケース6：出勤ボタンが正しく機能する
    public function test_出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $this->post(route('attendance.clock_in'));

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    //テストケース6：出勤は一日一回のみ
    public function test_出勤は一日一回のみ()
    {
        $user = User::factory()->clockedOut()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    //テストケース6：出勤時刻が勤怠一覧画面で確認できる
    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->post(route('attendance.clock_in'));

        $response = $this->get('/attendance/list');

        $expectedTime = $now->format('H:i');
        $response->assertSee($expectedTime);
    }

    //テストケース7：休憩ボタンが正しく機能する
    public function test_休憩ボタンが正しく機能する()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post(route('attendance.rest_in'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    //テストケース7：休憩は一日何回でもできる
    public function test_休憩は一日何回でもできる()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $this->post(route('attendance.rest_in'));
        $this->post(route('attendance.rest_out'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    //テストケース7：休憩戻ボタンが正しく機能する
    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $this->post(route('attendance.rest_in'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post(route('attendance.rest_out'));

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    //テストケース7：休憩戻は一日に何回でもできる
    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $this->post(route('attendance.rest_in'));
        $this->post(route('attendance.rest_out'));
        $this->post(route('attendance.rest_in'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    //テストケース7：休憩時刻が勤怠一覧画面で確認できる
    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $restInTime = Carbon::now();
        Carbon::setTestNow($restInTime);

        $this->post(route('attendance.rest_in'));

        $restOutTime = $restInTime->copy()->addMinutes(30);
        Carbon::setTestNow($restOutTime);

        $this->post(route('attendance.rest_out'));

        $response = $this->get('/attendance/list');

        $response->assertSee('0:30');
    }

    //テストケース8：退勤ボタンが正しく機能する
    public function test_退勤ボタンが正しく機能する()
    {
        $user = User::factory()->clockedIn()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $this->post(route('attendance.clock_out'));

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    //テストケース8：退勤時刻が勤怠一覧画面で確認できる
    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clockInTime = Carbon::today()->setTime(9, 0, 0);
        Carbon::setTestNow($clockInTime);
        $this->post(route('attendance.clock_in'));

        $clockOutTime = Carbon::today()->setTime(18, 0, 0);
        Carbon::setTestNow($clockOutTime);
        $this->post(route('attendance.clock_out'));

        $response = $this->get('/attendance/list');
        $response->assertSee($clockOutTime->format('H:i'));
    }
}
