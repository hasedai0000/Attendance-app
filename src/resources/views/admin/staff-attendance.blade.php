<x-common.admin-layout :title="$user['name'] . 'さんの勤怠'" active-page="staff">
  <!-- 月間ナビゲーション -->
  <x-common.month-navigation :current-month="\Carbon\Carbon::createFromFormat('Y-m', $month)" route-name="admin.staff.attendance" :route-params="['id' => $user['id']]" />

  <!-- 勤怠一覧テーブル -->
  <x-common.attendance-table :attendances="$attendances" :show-detail="true" />

  <!-- CSV出力ボタン -->
  <div class="action-button-container">
    <a href="{{ route('admin.staff.attendance.csv', ['userId' => $user['id'], 'month' => $month]) }}"
      class="action-button action-button-primary action-button-medium">
      CSV出力
    </a>
  </div>
</x-common.admin-layout>
