@extends('layouts.app')

@section('title', 'メール認証誘導')

@section('css')
<link rel="stylesheet" href="{{ asset('css/authentication.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section section--center">
    <p class="verify-email__text">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>
    <a href="http://localhost:8025" target="_blank" rel="noopener noreferrer" class="verify-email__button">認証はこちらから</a>
    <form action="{{ route('verification.send') }}" method="post">
      @csrf
      <button type="submit" class="verify-email__resend">認証メールを再送する</button>
    </form>
  </section>
</main>

@endsection
