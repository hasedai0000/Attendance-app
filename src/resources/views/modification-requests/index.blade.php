<x-common.attendance-layout title="申請一覧" active-page="requests">
  <!-- タブナビゲーション -->
  <x-common.tab-navigation :tabs="[
      ['id' => 'pending', 'label' => '承認待ち', 'active' => true],
      ['id' => 'approved', 'label' => '承認済み', 'active' => false],
  ]" />

  <!-- 承認待ちタブ -->
  <div id="pending-tab" class="tab-content active">
    @php
      $pendingRequestsWithStatus = collect($pendingRequests)
          ->map(function ($request) {
              $request['status'] = 'pending';
              return $request;
          })
          ->toArray();
    @endphp
    <x-common.requests-table :requests="$pendingRequestsWithStatus" :show-user="true" />
  </div>

  <!-- 承認済みタブ -->
  <div id="approved-tab" class="tab-content">
    @php
      $approvedRequestsWithStatus = collect($approvedRequests)
          ->map(function ($request) {
              $request['status'] = 'approved';
              return $request;
          })
          ->toArray();
    @endphp
    <x-common.requests-table :requests="$approvedRequestsWithStatus" :show-user="true" />
  </div>
</x-common.attendance-layout>
