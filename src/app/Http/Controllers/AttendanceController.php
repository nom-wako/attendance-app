<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = $this->getTodayAttendance();
        $status = 1;

        if ($attendance && !is_null($attendance->clock_in)) {
            if ($attendance->clock_out) {
                $status = 4;
            } else {
                $activeRest = Rest::where('attendance_id', $attendance->id)
                    ->whereNull('end_time')
                    ->first();
                $status = $activeRest ? 3 : 2;
            }
        }

        return view('attendance.index', compact('status'));
    }

    public function clockIn()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance) {
            if (is_null($attendance->clock_in)) {
                $attendance->update([
                    'clock_in' => Carbon::now(),
                ]);
                return back()->with('success', '出勤打刻が完了しました。');
            }
            return back()->with('error', 'すでに本日の出勤打刻が完了しています。');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
        ]);

        return back()->with('success', '出勤打刻が完了しました。');
    }

    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

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
        $attendance = $this->getTodayAttendance();

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
        $attendance = $this->getTodayAttendance();

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
                $monthlyData[$dateStr] = $newAttendance;
            }
        }

        return view('attendance.list', compact('monthlyData', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function adminIndex($date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $prevDate = $targetDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('date', $targetDate->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'targetDate', 'prevDate', 'nextDate'));
    }

    public function staffAttendanceList($id, $year = null, $month = null)
    {
        $staff = User::findOrFail($id);
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;
        $targetMonth = Carbon::create($year, $month, 1);
        $prevMonth = $targetMonth->copy()->subMonth();
        $nextMonth = $targetMonth->copy()->addMonth();
        $daysInMonth = $targetMonth->daysInMonth;
        $attendances = Attendance::with('rests')
            ->where('user_id', $staff->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $monthlyData = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = Carbon::create($year, $month, $i)->format('Y-m-d');
            if ($attendances->has($dateStr)) {
                $monthlyData[$dateStr] = $attendances->get($dateStr);
            } else {
                $newAttendance = Attendance::create([
                    'user_id' => $staff->id,
                    'date' => $dateStr,
                ]);
                $monthlyData[$dateStr] = $newAttendance;
            }
        }
        return view('admin.attendance.staff', compact('staff', 'monthlyData', 'targetMonth', 'prevMonth', 'nextMonth'));
    }

    public function exportCsv($user_id, $month)
    {
        $user = User::findOrFail($user_id);
        $attendances = Attendance::with('rests')
            ->where('user_id', $user_id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')
            ->get();

        $fileName = "勤怠一覧_{$user->name}_{$month}.csv";

        return response()->streamDownload(function () use ($attendances) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['日付', '出勤時間', '退勤時間', '休憩時間', '勤務時間']);
            foreach ($attendances as $attendance) {
                $totalRestMinutes = 0;
                foreach ($attendance->rests as $rest) {
                    if ($rest->start_time && $rest->end_time) {
                        $start = Carbon::parse($rest->start_time);
                        $end = Carbon::parse($rest->end_time);
                        $totalRestMinutes += $start->diffInMinutes($end);
                    }
                }
                $restTimeFormatted = sprintf('%02d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                $workTimeFormatted = '';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockIn = Carbon::parse($attendance->clock_in);
                    $clockOut = Carbon::parse($attendance->clock_out);
                    $grossWorkMinutes = $clockIn->diffInMinutes($clockOut);
                    $netWorkMinutes = max(0, $grossWorkMinutes - $totalRestMinutes);
                    $workTimeFormatted = sprintf('%02d:%02d', floor($netWorkMinutes / 60), $netWorkMinutes % 60);
                }

                fputcsv($stream, [
                    $attendance->date->format('Y-m-d'),
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $restTimeFormatted,
                    $workTimeFormatted,
                ]);
            }
            fclose($stream);
        }, $fileName);
    }

    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();
    }
}
