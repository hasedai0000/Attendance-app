@extends('layouts.app')

@section('title', '修正申請一覧')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
@endsection

@section('content')
  <div class="modification-requests-container">
    <!-- ヘッダー -->
    <x-attendance.header active-page="requests" />

    <!-- メインコンテンツ -->
    <div class="attendance-main">
      <x-common.messages />

      <!-- タイトル -->
      <x-common.page-title title="申請一覧" />

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
        <x-common.requests-table :requests="$pendingRequestsWithStatus" />
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
        <x-common.requests-table :requests="$approvedRequestsWithStatus" />
      </div>
    </div>
  </div>

  <style>
    .modification-requests-container {
      min-height: 100vh;
      background-color: #F0EFF2;
      display: flex;
      flex-direction: column;
    }

    .attendance-main {
      flex: 1;
      padding: 40px 306px;
      max-width: 1512px;
      margin: 0 auto;
      width: 100%;
    }

    /* レスポンシブデザイン */
    @media (max-width: 1200px) {
      .attendance-main {
        padding: 40px 20px;
      }
    }

    @media (max-width: 768px) {
      .attendance-main {
        padding: 20px 16px;
      }
    }
  </style>
@endsection
