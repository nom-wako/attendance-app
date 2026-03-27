<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
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

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/rest-in', [AttendanceController::class, 'restIn'])->name('attendance.rest_in');
    Route::post('/attendance/rest-out', [AttendanceController::class, 'restOut'])->name('attendance.rest_out');
    Route::get('/attendance/list/{year?}/{month?}', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}/', [AttendanceCorrectionController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/detail/{id}/', [AttendanceCorrectionController::class, 'update'])->name('attendance.update');
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'correctionList'])->name('stamp_correction_request.list');
});
