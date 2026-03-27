<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required', 'after:clock_in'],
            'remarks' => ['required', 'string'],
            'rests.*.start_time' => ['nullable', 'after_or_equal:clock_in', 'before:clock_out'],
            'rests.*.end_time' => ['nullable', 'after:rests.*.start_time', 'before_or_equal:clock_out'],
            'new_rest.start_time' => ['nullable', 'after_or_equal:clock_in', 'before:clock_out'],
            'new_rest.end_time' => ['nullable', 'after:new_rest.start_time', 'before_or_equal:clock_out'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時刻を入力してください',
            'clock_out.required' => '退勤時刻を入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'rests.*.start_time.after_or_equal' => '休憩時間が不適切な値です',
            'rests.*.start_time.before' => '休憩時間が不適切な値です',
            'new_rest.start_time.after_or_equal' => '休憩時間が不適切な値です',
            'new_rest.start_time.before' => '休憩時間が不適切な値です',
            'rests.*.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_rest.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_rest.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
