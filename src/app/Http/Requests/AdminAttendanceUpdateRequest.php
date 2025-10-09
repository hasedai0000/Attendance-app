<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return $this->user() && $this->user()->is_admin;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'start_time' => 'required|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'remarks' => 'nullable|string|max:1000',
      'breaks' => 'nullable|array',
      'breaks.*.start_time' => 'nullable|date_format:H:i',
      'breaks.*.end_time' => 'nullable|date_format:H:i|after:breaks.*.start_time',
    ];
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'start_time.required' => '出勤時間は必須です。',
      'start_time.date_format' => '出勤時間は正しい時刻形式（HH:MM）で入力してください。',
      'end_time.date_format' => '退勤時間は正しい時刻形式（HH:MM）で入力してください。',
      'end_time.after' => '退勤時間は出勤時間より後の時刻を入力してください。',
      'remarks.max' => '備考は1000文字以内で入力してください。',
      'breaks.*.start_time.date_format' => '休憩開始時間は正しい時刻形式（HH:MM）で入力してください。',
      'breaks.*.end_time.date_format' => '休憩終了時間は正しい時刻形式（HH:MM）で入力してください。',
      'breaks.*.end_time.after' => '休憩終了時間は休憩開始時間より後の時刻を入力してください。',
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    // 空の休憩時間エントリを削除
    if ($this->has('breaks')) {
      $breaks = array_filter($this->input('breaks', []), function ($break) {
        return !empty($break['start_time']) || !empty($break['end_time']);
      });
      $this->merge(['breaks' => array_values($breaks)]);
    }
  }
}
