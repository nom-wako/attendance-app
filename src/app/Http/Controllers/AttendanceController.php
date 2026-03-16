<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        $status = 1;

        if ($attendance) {
            if ($attendance->clock_out) {
                $status = 4;
            } else {
                $activeRest = Rest::where('attendance_id', $attendance->id)
                    ->whereNull('end_time')
                    ->first();
                if ($activeRest) {
                    $status = 3;
                } else {
                    $status = 2;
                }
            }
        }

        return view('attendance.index', compact('status'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'すでに本日の出勤打刻が完了しています。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', '出勤打刻が完了しました。');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤打刻がされていません。');
        }

        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'すでに退勤打刻が完了しています。');
        }

        $attendance->update([
            'clock_out' => Carbon::now()
        ]);

        return redirect()->back()->with('success', '退勤打刻が完了しました。');
    }

    public function restIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || $attendance->clock_out) {
            return redirect()->back()->with('error', '休憩打刻ができない状態です。');
        }

        $activeRest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->first();

        if ($activeRest) {
            return redirect()->back()->with('error', 'すでに休憩中です。');
        }

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', '休憩に入りました。');
    }

    public function restOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || $attendance->clock_out) {
            return redirect()->back()->with('error', '休憩打刻ができない状態です。');
        }

        $activeRest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->first();

        if (!$activeRest) {
            return redirect()->back()->with('error', '休憩中ではありません。');
        }

        $activeRest->update([
            'end_time' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', '休憩から戻りました。');
    }
}
