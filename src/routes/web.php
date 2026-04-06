<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 1 ? redirect('/admin/attendance/list') : redirect('/attendance');
    }
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
        Route::get('/list', [AttendanceCorrectionController::class, 'correctionList'])->name('list');
        Route::middleware('admin')->group(function () {
            Route::get('/approve/{attendance_correct_request_id}', [AttendanceCorrectionController::class, 'showApprove'])->name('approve');
            Route::post('/approve/{attendance_correct_request_id}', [AttendanceCorrectionController::class, 'processApprove'])->name('process');
        });
    });

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock_in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock_out');
        Route::post('/rest-in', [AttendanceController::class, 'restIn'])->name('rest_in');
        Route::post('/rest-out', [AttendanceController::class, 'restOut'])->name('rest_out');
        Route::get('/list/{year?}/{month?}', [AttendanceController::class, 'list'])->name('list');
        Route::get('/detail/{id}', [AttendanceCorrectionController::class, 'show'])->name('show');
        Route::put('/detail/{id}', [AttendanceCorrectionController::class, 'update'])->name('update');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/attendance/list/{date?}', [AttendanceController::class, 'adminIndex'])->name('attendance.list');
        Route::get('/staff/list', [UserController::class, 'staffList'])->name('staff.list');
        Route::get('/attendance/staff/{id}/{year?}/{month?}', [AttendanceController::class, 'staffAttendanceList'])->name('attendance.staff');
        Route::get('/attendance/{id}', [AttendanceCorrectionController::class, 'adminShow'])->name('attendance.detail');
        Route::post('/attendance/{id}', [AttendanceCorrectionController::class, 'adminUpdate'])->name('attendance.update');
        Route::get('/attendance/{user_id}/export/{month}', [AttendanceController::class, 'exportCsv'])->name('attendance.export');
    });
});
