<header class="header">
  <div class="header__logo">
    <a href="/" class="header__logo-link"><img src="{{ asset('img/common/logo.svg') }}" alt="COACHTECH"></a>
  </div>
  @auth
  <nav class="header__nav">
    <ul class="header__nav-list">
      @if (auth()->user()->hasVerifiedEmail())
      @if (auth()->user()->role === 1)
      <li><a href="/admin/attendance/list" class="header__nav-item">勤怠一覧</a></li>
      <li><a href="/admin/staff/list" class="header__nav-item">スタッフ一覧</a></li>
      <li><a href="/stamp_correction_request/list" class="header__nav-item">申請一覧</a></li>
      @else
      <li><a href="/attendance" class="header__nav-item">勤怠</a></li>
      <li><a href="/attendance/list" class="header__nav-item">勤怠一覧</a></li>
      <li><a href="/stamp_correction_request/list" class="header__nav-item">申請</a></li>
      @endif
      @endif
      <li>
        <form action="/logout" method="post">
          @csrf
          <button class="header__logout">ログアウト</button>
        </form>
      </li>
    </ul>
  </nav>
  @endauth
</header>
