<x-common.admin-layout title="勤怠詳細" active-page="attendance">
  <!-- 勤怠詳細カード -->
  <x-common.attendance-detail-card :attendance="$attendance" :show-actions="true" :action-route="route('admin.modification-requests.approve', $attendance->id)" action-method="PUT" />
</x-common.admin-layout>
