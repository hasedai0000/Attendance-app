<x-common.attendance-layout title="勤怠一覧" active-page="list">
  <!-- 月選択部分 -->
  <x-common.month-navigation :current-month="\Carbon\Carbon::createFromFormat('Y-m', $month)" route-name="attendance.list" />

  <!-- 勤怠テーブル -->
  <x-common.attendance-table :attendances="$attendances" />
</x-common.attendance-layout>
