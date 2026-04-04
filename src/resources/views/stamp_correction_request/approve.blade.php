@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title page__title--left">勤怠詳細</h1>
    @if (session('status'))
    <p class="form__status">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
    @foreach (array_unique($errors->all()) as $error)
    <p class="form__error">{{ $error }}</p>
    @endforeach
    @endif
    <div class="form__content">
      <div class="form__group">
        <p class="form__label">名前</p>
        <p class="form__text">{{ $correction->attendance->user->name }}</p>
      </div>
      <div class="form__group">
        <p class="form__label">日付</p>
        <p class="form__grid">
          <span class="form__left">{{ $correction->attendance->date->format('Y年') }}</span>
          <span class="form__center"></span>
          <span class="form__right">{{ $correction->attendance->date->format('n月j日') }}</span>
        </p>
      </div>
      <div class="form__group">
        <p class="form__label">出勤・退勤</p>
        <p class="form__grid">
          <span class="form__left">{{ $correction->clock_in->format('H:i') }}</span>
          <span class="form__center">～</span>
          <span class="form__right">{{ $correction->clock_out ? $correction->clock_out->format('H:i') : '' }}</span>
        </p>
      </div>
      @foreach ($correction->restCorrections as $index => $restCorrection)
      <div class="form__group">
        <p class="form__label">休憩{{ $index + 1 }}</p>
        <p class="form__grid">
          <span class="form__left">{{ $restCorrection->start_time ? $restCorrection->start_time->format('H:i') : '' }}</span>
          <span class="form__center">～</span>
          <span class="form__right">{{ $restCorrection->end_time ? $restCorrection->end_time->format('H:i') : '' }}</span>
        </p>
      </div>
      @endforeach
      <div class="form__group">
        <p class="form__label">備考</p>
        <p class="form__text">{!! nl2br(e($correction->remarks)) !!}</p>
      </div>
    </div>
    @if ($correction->status === 1)
    <form action="{{ route('stamp_correction_request.process', $correction->id) }}" method="post">
      @csrf
      <button type="submit" class="form__button">承認</button>
    </form>
    @else
    <button type="button" class="form__button" disabled>承認済み</button>
    @endif
  </section>
</main>

@endsection
