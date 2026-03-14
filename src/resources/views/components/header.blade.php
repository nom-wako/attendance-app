<header class="header">
  <div class="header__logo">
    <a href="/" class="header__logo-link"><img src="{{ asset('img/common/logo.svg') }}" alt="COACHTECH"></a>
  </div>
  @auth
  <nav class="header__nav">
    <ul>
      <li><a href="/">勤怠</a></li>
      <li><a href="/">勤怠一覧</a></li>
      <li><a href="/">申請</a></li>
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
