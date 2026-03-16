@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section section--center">
    @if (session('success'))
    <p class="alert-success">
      {{ session('success') }}
    </p>
    @endif
    @if (session('error'))
    <p class="alert-error">
      {{ session('error') }}
    </p>
    @endif
    <div class="status-label">
      @if ($status === 1)
      <p>勤務外</p>
      @elseif ($status === 2)
      <p>出勤中</p>
      @elseif ($status === 3)
      <p>休憩中</p>
      @elseif ($status === 4)
      <p>退勤済</p>
      @endif
    </div>
    <h1 id="current-date" class="attendance__date"></h1>
    <p id="current-time" class="attendance__time"></p>
    <div class="stamps">
      @if ($status === 1)
      <form action="{{ route('attendance.clock_in') }}" method="post">
        @csrf
        <button type="submit" class="stamps__button">出勤</button>
      </form>
      @elseif ($status === 2)
      <form action="{{ route('attendance.clock_out') }}" method="post">
        @csrf
        <button type="submit" class="stamps__button">退勤</button>
      </form>
      <form action="" method="post">
        @csrf
        <button type="submit" class="stamps__button stamps__button--rest">休憩入</button>
      </form>
      @elseif ($status === 3)
      <form action="" method="post">
        @csrf
        <button type="submit" class="stamps__button stamps__button--rest">休憩戻</button>
      </form>
      @elseif ($status === 4)
      <p>お疲れ様でした。</p>
      @endif
    </div>
  </section>
</main>

@endsection

@push('scripts')
<script src="{{ asset('js/attendance.js') }}"></script>
@endpush
