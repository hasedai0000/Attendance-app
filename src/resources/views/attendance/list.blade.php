@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
  <div class="attendance-list-container">
    <!-- ヘッダー -->
    <div class="attendance-header">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
      <nav class="attendance-nav">
        <a href="{{ route('attendance.index') }}" class="nav-item">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="nav-item active">勤怠一覧</a>
        <a href="{{ route('modification-requests.index') }}" class="nav-item">申請</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-item">ログアウト</button>
        </form>
      </nav>
    </div>

    <!-- メインコンテンツ -->
    <div class="attendance-main">
      @if (session('message'))
        <div class="message message-success">
          {{ session('message') }}
        </div>
      @endif

      @if (session('error'))
        <div class="message message-error">
          {{ session('error') }}
        </div>
      @endif

      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>勤怠一覧</h1>
      </div>

      <!-- 月選択部分 -->
      <div class="month-selector">
        @php
          $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $month);
          $prevMonth = $currentMonth->copy()->subMonth();
          $nextMonth = $currentMonth->copy()->addMonth();
        @endphp

        <a href="{{ route('attendance.list', ['month' => $prevMonth->format('Y-m')]) }}" class="month-nav">
          <img src="{{ asset('images/arrow-left.svg') }}" alt="前月" class="arrow-icon">
          前月
        </a>

        <div class="current-month">{{ $currentMonth->format('Y/m') }}</div>

        <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}" class="month-nav">
          翌月
          <img src="{{ asset('images/arrow-right.svg') }}" alt="翌月" class="arrow-icon">
        </a>
      </div>

      <!-- 勤怠テーブル -->
      <div class="attendance-table-container">
        <!-- テーブルヘッダー -->
        <div class="table-header">
          <div class="header-item">日付</div>
          <div class="header-item">出勤</div>
          <div class="header-item">退勤</div>
          <div class="header-item">休憩</div>
          <div class="header-item">合計</div>
          <div class="header-item">詳細</div>
        </div>

        <!-- 勤怠レコード -->
        @if (count($attendances) > 0)
          @foreach ($attendances as $attendance)
            <div class="attendance-row">
              <div class="row-item date">
                @php
                  $date = \Carbon\Carbon::parse($attendance['date'])->setTimezone('Asia/Tokyo');
                  $dayOfWeekNames = ['日', '月', '火', '水', '木', '金', '土'];
                  $dayOfWeekShort = $dayOfWeekNames[$date->dayOfWeek];
                @endphp
                {{ $date->format('m/d') }}({{ $dayOfWeekShort }})
              </div>
              <div class="row-item">
                {{ $attendance['start_time'] ? \Carbon\Carbon::parse($attendance['start_time'])->setTimezone('Asia/Tokyo')->format('H:i') : '-' }}
              </div>
              <div class="row-item">
                {{ $attendance['end_time'] ? \Carbon\Carbon::parse($attendance['end_time'])->setTimezone('Asia/Tokyo')->format('H:i') : '-' }}
              </div>
              <div class="row-item">
                @php
                  $totalBreakMinutes = 0;
                  if (isset($attendance['breaks']) && is_array($attendance['breaks'])) {
                      foreach ($attendance['breaks'] as $break) {
                          if (isset($break['start_time']) && isset($break['end_time'])) {
                              $start = \Carbon\Carbon::parse($break['start_time'])->setTimezone('Asia/Tokyo');
                              $end = \Carbon\Carbon::parse($break['end_time'])->setTimezone('Asia/Tokyo');
                              $totalBreakMinutes += $start->diffInMinutes($end);
                          }
                      }
                  }
                  $breakHours = intval($totalBreakMinutes / 60);
                  $breakMins = $totalBreakMinutes % 60;
                @endphp
                {{ $totalBreakMinutes > 0 ? sprintf('%d:%02d', $breakHours, $breakMins) : '-' }}
              </div>
              <div class="row-item">
                @if ($attendance['start_time'] && $attendance['end_time'])
                  @php
                    $start = \Carbon\Carbon::parse($attendance['start_time'])->setTimezone('Asia/Tokyo');
                    $end = \Carbon\Carbon::parse($attendance['end_time'])->setTimezone('Asia/Tokyo');
                    $totalMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
                    $workHours = intval($totalMinutes / 60);
                    $workMins = $totalMinutes % 60;
                  @endphp
                  {{ sprintf('%d:%02d', $workHours, $workMins) }}
                @else
                  -
                @endif
              </div>
              <div class="row-item">
                <a href="{{ route('attendance.detail', $attendance['id']) }}" class="detail-link">詳細</a>
              </div>
            </div>
          @endforeach
        @else
          <div class="no-data">
            この月の勤怠記録はありません。
          </div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .attendance-list-container {
      min-height: 100vh;
      background-color: #F0EFF2;
      display: flex;
      flex-direction: column;
    }

    .attendance-header {
      background-color: #000000;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 25px;
    }

    .logo {
      height: 36px;
      width: auto;
    }

    .attendance-nav {
      display: flex;
      gap: 40px;
      align-items: center;
    }

    .nav-item {
      color: #FFFFFF;
      text-decoration: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 24px;
      line-height: 1.21;
      transition: opacity 0.2s ease;
    }

    .nav-item:hover {
      opacity: 0.8;
    }

    .nav-item.active {
      color: #FFFFFF;
    }

    .attendance-main {
      flex: 1;
      padding: 40px 306px;
      max-width: 1512px;
      margin: 0 auto;
      width: 100%;
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 21px;
      margin-bottom: 40px;
    }

    .title-line {
      width: 8px;
      height: 40px;
      background-color: #000000;
    }

    .page-title h1 {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 30px;
      line-height: 1.21;
      color: #000000;
      margin: 0;
    }

    .month-selector {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 36px;
      margin-bottom: 30px;
    }

    .month-nav {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #737373;
      text-decoration: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      transition: opacity 0.2s ease;
    }

    .month-nav:hover {
      opacity: 0.8;
    }

    .arrow-icon {
      width: 20px;
      height: 15px;
    }

    .current-month {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 20px;
      line-height: 1.21;
      color: #000000;
    }

    .attendance-table-container {
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table-header {
      background-color: #FFFFFF;
      border-bottom: 3px solid #E1E1E1;
      display: grid;
      grid-template-columns: 148px 130px 130px 87px 87px 87px;
      padding: 15px 55px;
    }

    .header-item {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #737373;
      text-align: left;
    }

    .attendance-row {
      display: grid;
      grid-template-columns: 148px 130px 130px 87px 87px 87px;
      padding: 16px 55px;
      border-bottom: 2px solid #E1E1E1;
      transition: background-color 0.2s ease;
    }

    .attendance-row:hover {
      background-color: #f8f9fa;
    }

    .attendance-row:last-child {
      border-bottom: none;
    }

    .row-item {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #737373;
      display: flex;
      align-items: center;
    }

    .row-item.date {
      color: #737373;
    }

    .detail-link {
      color: #000000;
      text-decoration: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      transition: opacity 0.2s ease;
    }

    .detail-link:hover {
      opacity: 0.8;
    }

    .no-data {
      text-align: center;
      padding: 60px 20px;
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 16px;
      color: #737373;
    }

    .message {
      padding: 16px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 16px;
    }

    .message-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    /* レスポンシブデザイン */
    @media (max-width: 1200px) {
      .attendance-nav {
        gap: 20px;
      }

      .nav-item {
        font-size: 18px;
      }

      .attendance-main {
        padding: 40px 20px;
      }
    }

    @media (max-width: 768px) {
      .attendance-header {
        flex-direction: column;
        height: auto;
        padding: 20px;
        gap: 20px;
      }

      .attendance-nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
      }

      .nav-item {
        font-size: 16px;
      }

      .attendance-main {
        padding: 20px 16px;
      }

      .page-title h1 {
        font-size: 24px;
      }

      .table-header,
      .attendance-row {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 16px 20px;
      }

      .header-item,
      .row-item {
        text-align: left;
        padding: 4px 0;
      }

      .header-item::before,
      .row-item::before {
        content: attr(data-label);
        font-weight: 700;
        color: #737373;
        margin-right: 8px;
        min-width: 60px;
        display: inline-block;
      }
    }

    @media (max-width: 480px) {
      .page-title h1 {
        font-size: 20px;
      }

      .month-selector {
        gap: 20px;
      }

      .current-month {
        font-size: 18px;
      }

      .attendance-table-container {
        font-size: 14px;
      }
    }
  </style>
@endsection
