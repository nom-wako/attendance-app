@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title page__title--left">勤怠一覧</h1>
    <div class="month-nav">
      <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="month-nav__prev"><span class="month-nav__arrow month-nav__arrow--prev"></span>前月</a>
      <h2 class="month-nav__heading">{{ $currentMonth->format('Y/m') }}</h2>
      <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="month-nav__next">翌月<span class="month-nav__arrow"></span></a>
    </div>
    <div class="attendance-table">
      <table>
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($monthlyData as $dateStr => $attendance)
          @php
          $dateObj = \Carbon\Carbon::parse($dateStr);
          $days = ['日', '月', '火', '水', '木', '金', '土'];
          $dayOfWeek = $days[$dateObj->dayOfWeek];
          $dateDisplay = $dateObj->format('m/d') . '(' . $dayOfWeek . ')';

          $clockIn = '';
          $clockOut = '';
          $restDisplay = '';
          $workDisplay = '';

          if ($attendance) {
          $clockIn = $attendance->clock_in ? $attendance->clock_in->format('H:i') : '';
          $clockOut = $attendance->clock_out ? $attendance->clock_out->format('H:i') : '';

          $restSumMinutes = 0;
          foreach ($attendance->rests as $rest) {
          if ($rest->start_time && $rest->end_time) {
          $restSumMinutes += $rest->start_time->diffInMinutes($rest->end_time);
          }
          }
          if ($restSumMinutes > 0) {
          $restDisplay = sprintf('%d:%02d', floor($restSumMinutes / 60), $restSumMinutes % 60);
          }

          if ($attendance->clock_in && $attendance->clock_out) {
          $totalWorkMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out) - $restSumMinutes;
          $workDisplay = sprintf('%d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);
          }
          }
          @endphp

          <tr>
            <td>{{ $dateDisplay }}</td>
            <td>{{ $clockIn }}</td>
            <td>{{ $clockOut }}</td>
            <td>{{ $restDisplay }}</td>
            <td>{{ $workDisplay }}</td>
            <td><a href="#">詳細</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
</main>

@endsection
