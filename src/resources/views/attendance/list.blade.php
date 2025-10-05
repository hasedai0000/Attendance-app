@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
@endsection

@section('content')
  <div class="attendance-list-container">
    <!-- ヘッダー -->
    <x-attendance.header active-page="list" />

    <!-- メインコンテンツ -->
    <div class="attendance-main">
      <x-common.messages />

      <!-- タイトル -->
      <x-common.page-title title="勤怠一覧" />

      <!-- 月選択部分 -->
      <x-common.month-navigation :current-month="\Carbon\Carbon::createFromFormat('Y-m', $month)" route-name="attendance.list" />

      <!-- 勤怠テーブル -->
      <x-common.attendance-table :attendances="$attendances" />
    </div>
  </div>

  <style>
    .attendance-list-container {
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
