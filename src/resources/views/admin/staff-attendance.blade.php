@extends('layouts.app')

@section('title', 'スタッフ勤怠一覧')

@section('content')
  <div class="admin-staff-attendance-container">
    <!-- ヘッダー -->
    <x-admin.header active-page="staff" />

    <!-- メインコンテンツ -->
    <div class="admin-content">
      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>{{ $user['name'] }}さんの勤怠</h1>
      </div>

      <!-- 月間ナビゲーション -->
      <div class="month-navigation">
        @php
          $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $month);
          $prevMonth = $currentMonth->copy()->subMonth();
          $nextMonth = $currentMonth->copy()->addMonth();
        @endphp

        <a href="{{ route('admin.staff.attendance', ['id' => $user['id'], 'month' => $prevMonth->format('Y-m')]) }}"
          class="nav-arrow">
          <img src="{{ asset('images/arrow-left.svg') }}" alt="前月" class="arrow-icon">
        </a>
        <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
        <a href="{{ route('admin.staff.attendance', ['id' => $user['id'], 'month' => $nextMonth->format('Y-m')]) }}"
          class="nav-arrow">
          <img src="{{ asset('images/arrow-right.svg') }}" alt="翌月" class="arrow-icon">
        </a>
      </div>

      <!-- 勤怠一覧テーブル -->
      <div class="attendance-table-container">
        <table class="attendance-table">
          <thead>
            <tr class="table-header">
              <th>日付</th>
              <th>出勤</th>
              <th>退勤</th>
              <th>休憩</th>
              <th>合計</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attendances as $attendance)
              <tr class="table-row">
                <td class="date-cell">
                  {{ \Carbon\Carbon::parse($attendance['date'])->format('m/d') }}({{ \Carbon\Carbon::parse($attendance['date'])->locale('ja')->isoFormat('ddd') }})
                </td>
                <td class="time-cell">
                  {{ $attendance['start_time'] ? \Carbon\Carbon::parse($attendance['start_time'])->format('H:i') : '' }}
                </td>
                <td class="time-cell">
                  {{ $attendance['end_time'] ? \Carbon\Carbon::parse($attendance['end_time'])->format('H:i') : '' }}
                </td>
                <td class="time-cell">
                  @if (isset($attendance['breaks']) && count($attendance['breaks']) > 0)
                    @php
                      $totalBreakMinutes = 0;
                      foreach ($attendance['breaks'] as $break) {
                          // オブジェクトと配列の両方に対応
                          $startTime = is_array($break) ? $break['start_time'] : $break->start_time;
                          $endTime = is_array($break) ? $break['end_time'] : $break->end_time;

                          if ($startTime && $endTime) {
                              $start = \Carbon\Carbon::parse($startTime);
                              $end = \Carbon\Carbon::parse($endTime);
                              $totalBreakMinutes += $start->diffInMinutes($end);
                          }
                      }
                      $hours = intval($totalBreakMinutes / 60);
                      $minutes = $totalBreakMinutes % 60;
                    @endphp
                    {{ sprintf('%d:%02d', $hours, $minutes) }}
                  @else
                    -
                  @endif
                </td>
                <td class="time-cell">
                  @if ($attendance['start_time'] && $attendance['end_time'])
                    @php
                      $start = \Carbon\Carbon::parse($attendance['start_time']);
                      $end = \Carbon\Carbon::parse($attendance['end_time']);
                      $workMinutes = $start->diffInMinutes($end);
                      // 休憩時間を引く
                      if (isset($attendance['breaks'])) {
                          foreach ($attendance['breaks'] as $break) {
                              // オブジェクトと配列の両方に対応
                              $startTime = is_array($break) ? $break['start_time'] : $break->start_time;
                              $endTime = is_array($break) ? $break['end_time'] : $break->end_time;

                              if ($startTime && $endTime) {
                                  $breakStart = \Carbon\Carbon::parse($startTime);
                                  $breakEnd = \Carbon\Carbon::parse($endTime);
                                  $workMinutes -= $breakStart->diffInMinutes($breakEnd);
                              }
                          }
                      }
                      $workHours = intval($workMinutes / 60);
                      $workMins = $workMinutes % 60;
                    @endphp
                    {{ sprintf('%d:%02d', $workHours, $workMins) }}
                  @else
                    -
                  @endif
                </td>
                <td class="detail-cell">
                  <a href="{{ route('admin.attendance.detail', $attendance['id']) }}" class="detail-link">詳細</a>
                </td>
              </tr>
            @empty
              <tr class="table-row">
                <td colspan="6" class="no-data">この月の勤怠データはありません</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- CSV出力ボタン -->
      <div class="csv-button-container">
        <a href="{{ route('admin.staff.attendance.csv', ['userId' => $user['id'], 'month' => $month]) }}"
          class="csv-button">
          CSV出力
        </a>
      </div>
    </div>
  </div>

  <style>
    .admin-staff-attendance-container {
      min-height: 100vh;
      background-color: #F0EFF2;
      display: flex;
      flex-direction: column;
    }

    .admin-content {
      flex: 1;
      padding: 40px 302px;
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

    .month-navigation {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin-bottom: 40px;
    }

    .nav-arrow {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 15px;
      text-decoration: none;
      transition: opacity 0.2s ease;
    }

    .nav-arrow:hover {
      opacity: 0.7;
    }

    .arrow-icon {
      width: 100%;
      height: 100%;
    }

    .current-month {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 20px;
      line-height: 1.21;
      color: #000000;
      padding: 0 20px;
    }

    .attendance-table-container {
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 40px;
    }

    .attendance-table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-header {
      background-color: #FFFFFF;
      border-bottom: 3px solid #E1E1E1;
    }

    .table-header th {
      padding: 15px 36px;
      text-align: left;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
    }

    .table-row {
      border-bottom: 2px solid #E1E1E1;
    }

    .table-row:last-child {
      border-bottom: none;
    }

    .table-row td {
      padding: 15px 36px;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
    }

    .date-cell {
      text-align: left;
    }

    .time-cell {
      text-align: center;
    }

    .detail-link {
      color: #000000;
      text-decoration: none;
      transition: opacity 0.2s ease;
    }

    .detail-link:hover {
      opacity: 0.7;
    }

    .no-data {
      text-align: center;
      color: #737373;
      font-style: italic;
    }

    .csv-button-container {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 40px;
    }

    .csv-button {
      background-color: #000000;
      color: #FFFFFF;
      padding: 11px 39px;
      border-radius: 5px;
      text-decoration: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      transition: opacity 0.2s ease;
    }

    .csv-button:hover {
      opacity: 0.8;
    }

    /* レスポンシブデザイン */
    @media (max-width: 1200px) {
      .admin-content {
        padding: 40px 20px;
      }
    }

    @media (max-width: 768px) {
      .admin-content {
        padding: 20px 16px;
      }

      .page-title h1 {
        font-size: 24px;
      }

      .attendance-table-container {
        overflow-x: auto;
      }

      .table-header th,
      .table-row td {
        padding: 12px 16px;
        font-size: 14px;
      }

      .csv-button {
        font-size: 18px;
        padding: 10px 30px;
      }
    }
  </style>
@endsection
