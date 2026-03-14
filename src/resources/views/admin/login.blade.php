@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/authentication.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title">管理者ログイン</h1>
    <form action="/login" method="post" class="form">
      @csrf
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
      <button type="submit" class="form__button">管理者ログインする</button>
    </form>
  </section>
</main>

@endsection
