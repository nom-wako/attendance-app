<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
  /**
   * ログイン成功時のリダイレクト先を権限によって振り分ける
   */
  public function toResponse($request)
  {
    $user = Auth::user();

    if ($user->role === 1) {
      return redirect()->intended('/admin/attendance/list');
    }

    // それ以外（一般ユーザー）だったら
    return redirect()->intended('/attendance');
  }
}
