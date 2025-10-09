<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ModificationRequestRequest extends FormRequest
{
  /**
   * 認可
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * バリデーションルール
   */
  public function rules(): array
  {
    return [
      'attendance_id' => 'required|exists:attendances,id',
      'start_time' => 'nullable|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'remarks' => 'required|string|max:1000',
      'breaks' => 'nullable|array',
      'breaks.*.start_time' => 'nullable|date_format:H:i',
      'breaks.*.end_time' => 'nullable|date_format:H:i|after:breaks.*.start_time',
    ];
  }

  /**
   * カスタムバリデーションメッセージ
   */
  public function messages(): array
  {
    return [
      'attendance_id.required' => '勤怠IDが必要です',
      'attendance_id.exists' => '指定された勤怠データが存在しません',
      'start_time.date_format' => '出勤時間は正しい時刻形式で入力してください',
      'end_time.date_format' => '退勤時間は正しい時刻形式で入力してください',
      'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
      'remarks.required' => '備考を記入してください',
      'remarks.string' => '備考は文字列で入力してください',
      'remarks.max' => '備考は1000文字以内で入力してください',
      'breaks.*.start_time.date_format' => '休憩開始時間は正しい時刻形式で入力してください',
      'breaks.*.end_time.date_format' => '休憩終了時間は正しい時刻形式で入力してください',
      'breaks.*.end_time.after' => '休憩時間が不適切な値です',
    ];
  }

  /**
   * バリデーション後の処理
   */
  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $startTime = $this->input('start_time');
      $endTime = $this->input('end_time');
      $breaks = $this->input('breaks', []);

      // 出勤時間と退勤時間の整合性チェック
      if ($startTime && $endTime) {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        if ($start->gte($end)) {
          $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
        }
      }

      // 休憩時間の整合性チェック
      foreach ($breaks as $index => $break) {
        if (!empty($break['start_time']) && !empty($break['end_time'])) {
          try {
            $breakStart = Carbon::createFromFormat('H:i', $break['start_time']);
            $breakEnd = Carbon::createFromFormat('H:i', $break['end_time']);

            // 休憩時間が出勤時間より前または退勤時間より後の場合
            if ($startTime) {
              $workStart = Carbon::createFromFormat('H:i', $startTime);
              if ($breakStart->lt($workStart)) {
                $validator->errors()->add("breaks.{$index}.start_time", '休憩時間が不適切な値です');
              }
            }

            if ($endTime) {
              $workEnd = Carbon::createFromFormat('H:i', $endTime);
              if ($breakEnd->gt($workEnd)) {
                $validator->errors()->add("breaks.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
              }
            }
          } catch (\Exception $e) {
            // 時刻形式が不正な場合は既存のバリデーションルールでエラーになる
          }
        }
      }
    });
  }
}
