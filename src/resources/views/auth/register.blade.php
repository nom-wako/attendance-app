@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/authentication.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title">会員登録</h1>
    <form action="/register" method="post" class="form">
      @csrf
      <div class="form__group">
        <label for="name" class="form__label">名前</label>
        <input type="text" name="name" id="name" class="form__input" value="{{ old('name') }}">
        <div class="form__error">
          @error('name')
          {{ $message }}
          @enderror
        </div>
      </div>
      <div class="form__group">
        <label for="email" class="form__label">メールアドレス</label>
        <input type="email" name="email" id="email" class="form__input" value="{{ old('email') }}">
        <div class="form__error">
          @error('email')
          {{ $message }}
          @enderror
        </div>
      </div>
      <div class="form__group">
        <label for="password" class="form__label">パスワード</label>
        <input type="password" name="password" id="password" class="form__input">
        <div class="form__error">
          @error('password')
          {{ $message }}
          @enderror
        </div>
      </div>
      <div class="form__group">
        <label for="password_confirmation" class="form__label">確認用パスワード</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form__input">
        <div class="form__error">
          @error('password_confirmation')
          {{ $message }}
          @enderror
        </div>
      </div>
      <button type="submit" class="form__button">登録する</button>
    </form>
    <a href="/login" class="link">ログインはこちら</a>
  </section>
</main>

@endsection
