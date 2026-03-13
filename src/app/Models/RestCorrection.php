<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_correction_id', 'rest_id', 'start_time', 'end_time'];

    public function attendanceCorrection()
    {
        return $this->belongsTo(AttendanceCorrection::class);
    }

    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }
}
