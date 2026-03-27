<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;
use App\Models\RestCorrection;
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

    public function list($year = null, $month = null)
    {
        $user = Auth::user();

        if (!$year || !$month) {
            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;
        }

        $currentMonth = Carbon::create($year, $month, 1);
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $attendances = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $daysInMonth = $currentMonth->daysInMonth;
        $monthlyData = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = Carbon::create($year, $month, $i)->format('Y-m-d');

            if ($attendances->has($dateStr)) {
                $monthlyData[$dateStr] = $attendances->get($dateStr);
            } else {
                $newAttendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $dateStr,
                ]);
            }

            $monthlyData[$dateStr] = $attendances->has($dateStr) ? $attendances->get($dateStr) : null;
        }

        return view('attendance.list', compact('monthlyData', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        if ($attendance->user_id !== Auth::id()) {
            return redirect()->route('attendance.list')->with('error', '不正なアクセスです。');
        }
        return view('attendance.show', compact('attendance'));
    }

    public function update(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->user_id !== Auth::id()) {
            return redirect()->route('attendance.list')->with('error', '不正なアクセスです。');
        }
        $dataStr = $attendance->date->format('Y-m-d');

        $correction = new AttendanceCorrection();
        $correction->attendance_id = $attendance->id;

        if ($request->clock_in) {
            $correction->clock_in = $dataStr . ' ' . $request->clock_in . ':00';
        }
        if ($request->clock_out) {
            $correction->clock_out = $dataStr . ' ' . $request->clock_out . ':00';
        }

        $correction->remarks = $request->remarks;
        $correction->status = 1;
        $correction->save();

        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restData) {
                $restCorrection = new RestCorrection();
                $restCorrection->attendance_correction_id = $correction->id;
                $restCorrection->rest_id = $restId;

                if ($restData['start_time']) {
                    $restCorrection->start_time = $dataStr . ' ' . $restData['start_time'] . ':00';
                }
                if ($restData['end_time']) {
                    $restCorrection->end_time = $dataStr . ' ' . $restData['end_time'] . ':00';
                }
                $restCorrection->save();
            }
        }

        if ($request->has('new_rest')) {
            $newRestData = $request->new_rest;

            if (!empty($newRestData['start_time']) || !empty($newRestData['end_time'])) {
                $newRestCorrection = new RestCorrection();
                $newRestCorrection->attendance_correction_id = $correction->id;

                $newRestCorrection->rest_id = null;

                if (!empty($newRestData['start_time'])) {
                    $newRestCorrection->start_time = $dataStr . ' ' . $newRestData['start_time'] . ':00';
                }
                if (!empty($newRestData['end_time'])) {
                    $newRestCorrection->end_time = $dataStr . ' ' . $newRestData['end_time'] . ':00';
                }
                $newRestCorrection->save();
            }
        }

        return redirect()->route('attendance.show', $id)->with('success', '修正申請を提出しました！');
    }
}
