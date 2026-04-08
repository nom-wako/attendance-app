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
    <form method="post" action="{{ route('admin.attendance.update', $attendance->id) }}" class="form">
      @csrf
      <div class="form__content">
        <div class="form__group">
          <p class="form__label">名前</p>
          <p class="form__text">{{ $attendance->user->name }}</p>
        </div>
        <div class="form__group">
          <p class="form__label">日付</p>
          <p class="form__grid">
            <span class="form__left">{{ $attendance->date->format('Y年') }}</span>
            <span class="form__center"></span>
            <span class="form__right">{{ $attendance->date->format('n月j日') }}</span>
          </p>
        </div>
        @if ($pendingCorrection)
        <div class="form__group">
          <p class="form__label">出勤・退勤</p>
          <p class="form__grid">
            <span class="form__left">{{ $pendingCorrection->clock_in ? $pendingCorrection->clock_in->format('H:i') : '' }}</span>
            <span class="form__center">～</span>
            <span class="form__right">{{ $pendingCorrection->clock_out ? $pendingCorrection->clock_out->format('H:i') : '' }}</span>
          </p>
        </div>
        @foreach ($pendingCorrection->restCorrections as $index => $restCorrection)
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
          <p class="form__text">{!! nl2br(e($pendingCorrection->remarks)) !!}</p>
        </div>
        @else
        <div class="form__group">
          <p class="form__label">出勤・退勤</p>
          <p class="form__grid">
            <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}" class="form__left">
            <span class="form__center">～</span>
            <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}" class="form__right">
          </p>
        </div>
        @foreach ($attendance->rests as $index => $rest)
        <div class="form__group">
          <p class="form__label">休憩{{ $index + 1 }}</p>
          <p class="form__grid">
            <input type="time" name="rests[{{ $rest->id }}][start_time]" value="{{ old('rests.'.$rest->id.'.start_time', $rest->start_time ? $rest->start_time->format('H:i') : '') }}">
            <span class="form__center">～</span>
            <input type="time" name="rests[{{ $rest->id }}][end_time]" value="{{ old('rests.'.$rest->id.'.end_time', $rest->end_time ? $rest->end_time->format('H:i') : '') }}">
          </p>
        </div>
        @endforeach
        <div class="form__group">
          <p class="form__label">休憩{{ $attendance->rests->count() + 1 }}</p>
          <p class="form__grid">
            <input type="time" name="new_rest[start_time]" value="{{ old('new_rest.start_time') }}">
            <span class="form__center">～</span>
            <input type="time" name="new_rest[end_time]" value="{{ old('new_rest.end_time') }}">
          </p>
        </div>
        <div class="form__group">
          <p class="form__label">備考</p>
          <textarea name="remarks" class="form__textarea"></textarea>
        </div>
        @endif
      </div>
      @if ($pendingCorrection)
      <p class="form__notes">*承認待ちのため修正はできません。</p>
      @else
      <button type="submit" class="form__button">修正</button>
      @endif
    </form>
  </section>
</main>

@endsection
