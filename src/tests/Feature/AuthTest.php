<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //テストケース1：会員登録時名前未入力のバリデーションチェック
    public function test_会員登録時名前未入力のバリデーションチェック()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertInvalid(['name' => 'お名前を入力してください']);
    }

    //テストケース1：会員登録時メールアドレス未入力のバリデーションチェック
    public function test_会員登録時メールアドレス未入力のバリデーションチェック()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertInvalid(['email' => 'メールアドレスを入力してください']);
    }

    //テストケース1：会員登録時パスワード8文字未満のバリデーションチェック
    public function test_会員登録時パスワード8文字未満のバリデーションチェック()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertInvalid(['password' => 'パスワードは8文字以上で入力してください']);
    }

    //テストケース1：会員登録時パスワードが一致しないバリデーションチェック
    public function test_会員登録時パスワードが一致しないバリデーションチェック()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertInvalid(['password' => 'パスワードと一致しません']);
    }

    //テストケース1：会員登録時パスワード未入力のバリデーションチェック
    public function test_会員登録時パスワード未入力のバリデーションチェック()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertInvalid(['password' => 'パスワードを入力してください']);
    }

    //テストケース1：会員登録時データが正常に保存される
    public function test_会員登録時データが正常に保存される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    //テストケース2：ログイン時メールアドレス未入力のバリデーションチェック
    public function test_ログイン時メールアドレス未入力のバリデーションチェック()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123'
        ]);

        $response->assertInvalid(['email' => 'メールアドレスを入力してください']);
    }

    //テストケース2：ログイン時パスワード未入力のバリデーションチェック
    public function test_ログイン時パスワード未入力のバリデーションチェック()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertInvalid(['password' => 'パスワードを入力してください']);
    }

    //テストケース2：ログイン時登録内容と一致しないバリデーションチェック
    public function test_ログイン時登録内容と一致しないバリデーションチェック()
    {
        $user = User::factory()->create([
            'email' => 'correct@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertInvalid(['email' => 'ログイン情報が登録されていません']);
    }

    //テストケース3：管理者用メールアドレス未入力のバリデーションチェック
    public function test_管理者用メールアドレス未入力のバリデーションチェック()
    {
        $admin = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertInvalid(['email' => 'メールアドレスを入力してください']);
    }

    //テストケース3：管理者用パスワード未入力のバリデーションチェック
    public function test_管理者用パスワード未入力のバリデーションチェック()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertInvalid(['password' => 'パスワードを入力してください']);
    }

    //テストケース3：管理者用登録内容と一致しないバリデーションチェック
    public function test_管理者用登録内容と一致しないバリデーションチェック()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertInvalid(['email' => 'ログイン情報が登録されていません']);
    }

    //テストケース16：会員登録後認証メールが送信される
    public function test_会員登録後認証メールが送信される()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //テストケース16：認証はこちらからボタンが正しく機能する
    public function test_認証はこちらからボタンが正しく機能する()
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->get('/email/verify');
        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');
        $mailhogUrl = 'http://localhost:8025';
        $response->assertSee('href="' . $mailhogUrl . '"', false);
    }

    //テストケース16：メール認証完了後勤怠登録画面に遷移
    public function test_メール認証完了後勤怠登録画面に遷移()
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect('/attendance?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
