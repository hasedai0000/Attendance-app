<x-common.attendance-layout title="{{ $date->format('Y年m月d日') }}の勤怠" active-page="list">
  <!-- 日付ナビゲーション -->
  <x-common.date-navigation :date="$date" route-name="admin.attendance.daily" />

  <!-- 勤怠一覧テーブル -->
  <x-common.attendance-table :attendances="$attendances" :show-edit="true" :show-user-names="true" />
</x-common.attendance-layout>
