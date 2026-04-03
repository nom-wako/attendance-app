<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAttendanceRequest;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;
use App\Models\RestCorrection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionController extends Controller
{
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

    public function correctionList()
    {
        $user = Auth::user();
        $query = AttendanceCorrection::with(['attendance.user']);

        if ($user->role !== 1) {
            $query->whereHas('attendance', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $corrections = $query->orderBy('created_at', 'desc')->get();
        $pendingCorrections = $corrections->where('status', 1);
        $approvedCorrections = $corrections->whereIn('status', [2, 3]);

        return view('stamp_correction_request.list', compact('pendingCorrections', 'approvedCorrections'));
    }

    public function adminShow($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        return view('admin.attendance.detail', compact('attendance'));
    }

    public function adminUpdate(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);

        if ($request->has('rests')) {
            foreach ($request->rests as $restData) {
                if (isset($restData['id'])) {
                    $rest = Rest::findOrFail($restData['id']);
                    $rest->update([
                        'start_time' => $restData['start_time'],
                        'end_time' => $restData['end_time'],
                    ]);
                }
            }
        }

        if ($request->has('new_rest')) {
            $newRestData = $request->new_rest;
            if (!empty($newRestData['start_time']) || !empty($newRestData['end_time'])) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $newRestData['start_time'],
                    'end_time' => $newRestData['end_time'],
                ]);
            }
        }

        return back()->with('status', '勤怠データを修正しました。');
    }
}
