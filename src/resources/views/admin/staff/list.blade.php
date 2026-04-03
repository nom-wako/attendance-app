@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

@include('components.header')
<main>
  <section class="section">
    <h1 class="page__title page__title--left">スタッフ一覧</h1>
    <div class="attendance-table">
      <table>
        <thead>
          <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($staffs as $staff)
          <tr>
            <td>{{ $staff->name }}</td>
            <td>{{ $staff->email }}</td>
            <td>
              <a href="{{ url('admin/attendance/staff/' . $staff->id) }}">詳細</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
</main>

@endsection
