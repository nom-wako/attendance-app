@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title page__title--left">申請一覧</h1>
    <ul class="tab-list">
      <li class="tab-list__item is-active" tabindex="0">承認待ち</li>
      <li class="tab-list__item" tabindex="0">承認済み</li>
    </ul>
    <div class="attendance-table attendance-table--request tab-content is-active">
      <table>
        <thead>
          <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pendingCorrections as $correction)
          <tr>
            <td>承認待ち</td>
            <td>{{ $correction->attendance->user->name }}</td>
            <td>{{ $correction->attendance->date->format('Y/m/d') }}</td>
            <td>{{ Str::limit($correction->remarks, 18, '…') }}</td>
            <td>{{ $correction->created_at->format('Y/m/d') }}</td>
            <td>
              @if (Auth::user()->role === 1)
              <a href="#">詳細</a>
              @else
              <a href="{{ route('attendance.show', $correction->attendance->id) }}">詳細</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="attendance-table attendance-table--request tab-content">
      <table>
        <thead>
          <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($approvedCorrections as $correction)
          <tr>
            <td>承認済み</td>
            <td>{{ $correction->attendance->user->name }}</td>
            <td>{{ $correction->attendance->date->format('Y/m/d') }}</td>
            <td>{{ Str::limit($correction->remarks, 30) }}</td>
            <td>{{ $correction->created_at->format('Y/m/d') }}</td>
            <td>
              @if (Auth::user()->role === 1)
              <a href="#">詳細</a>
              @else
              <a href="{{ route('attendance.show', $correction->attendance->id) }}">詳細</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
</main>

@endsection

@push('scripts')
<script src="{{ asset('js/stamp_correction_request.js') }}"></script>
@endpush
