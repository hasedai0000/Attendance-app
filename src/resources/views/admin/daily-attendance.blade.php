@extends('layouts.app')

@section('title', '管理者ダッシュボード')

@section('content')
  <div class="admin-attendance-container">
    <!-- ヘッダー -->
    <x-admin.header active-page="attendance" />

    <!-- メインコンテンツ -->
    <div class="admin-content">
      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>{{ $date->format('Y年m月d日') }}の勤怠</h1>
      </div>

      <!-- 日付ナビゲーション -->
      <div class="date-navigation">
        <div class="date-nav-left">
          <button class="date-nav-btn" onclick="changeDate(-1)">
            <img src="{{ asset('images/arrow-left.svg') }}" alt="前日" class="arrow-icon">
            前日
          </button>
        </div>

        <div class="current-date">
          <div class="date-display">{{ $date->format('Y/m/d') }}</div>
          <img src="{{ asset('images/calendar-icon.svg') }}" alt="カレンダー" class="calendar-icon">
        </div>

        <div class="date-nav-right">
          <button class="date-nav-btn" onclick="changeDate(1)">
            翌日
            <img src="{{ asset('images/arrow-right.svg') }}" alt="翌日" class="arrow-icon">
          </button>
        </div>
      </div>

      <!-- 勤怠一覧テーブル -->
      <div class="attendance-table-container">
        <table class="attendance-table">
          <thead>
            <tr class="table-header">
              <th>名前</th>
              <th>出勤</th>
              <th>退勤</th>
              <th>休憩</th>
              <th>合計</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @forelse($todayAttendances as $attendance)
              @php
                // 配列データから値を取得
                $userName = $attendance['user']['name'] ?? '不明';
                $clockIn = $attendance['start_time']
                    ? \Carbon\Carbon::parse($attendance['start_time'])->format('H:i')
                    : '-';
                $clockOut = $attendance['end_time']
                    ? \Carbon\Carbon::parse($attendance['end_time'])->format('H:i')
                    : '-';

                // 休憩時間の計算
                $totalBreakMinutes = 0;
                if (isset($attendance['breaks']) && is_array($attendance['breaks'])) {
                    foreach ($attendance['breaks'] as $break) {
                        if (isset($break['start_time']) && isset($break['end_time'])) {
                            $start = \Carbon\Carbon::parse($break['start_time']);
                            $end = \Carbon\Carbon::parse($break['end_time']);
                            $totalBreakMinutes += $start->diffInMinutes($end);
                        }
                    }
                }
                $breakHours = intval($totalBreakMinutes / 60);
                $breakMinutes = $totalBreakMinutes % 60;
                $breakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);

                // 勤務時間の計算
                $totalWorkMinutes = 0;
                if ($attendance['start_time'] && $attendance['end_time']) {
                    $start = \Carbon\Carbon::parse($attendance['start_time']);
                    $end = \Carbon\Carbon::parse($attendance['end_time']);
                    $totalWorkMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
                }
                $workHours = intval($totalWorkMinutes / 60);
                $workMinutes = $totalWorkMinutes % 60;
                $workTime = sprintf('%d:%02d', $workHours, $workMinutes);
              @endphp
              <tr class="table-row">
                <td class="staff-name">{{ $userName }}</td>
                <td class="time-cell">{{ $clockIn }}</td>
                <td class="time-cell">{{ $clockOut }}</td>
                <td class="time-cell">{{ $breakTime }}</td>
                <td class="time-cell">{{ $workTime }}</td>
                <td class="detail-cell">
                  <a href="{{ route('admin.attendance.detail', $attendance['id']) }}" class="detail-link">詳細</a>
                </td>
              </tr>
            @empty
              <tr class="table-row">
                <td colspan="6" class="no-data">本日の勤怠データがありません</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <style>
    .admin-attendance-container {
      min-height: 100vh;
      background-color: #F0EFF2;
      display: flex;
      flex-direction: column;
    }

    .admin-content {
      flex: 1;
      padding: 40px 306px;
      max-width: 1512px;
      margin: 0 auto;
      width: 100%;
    }

    .date-navigation {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 40px;
      margin-bottom: 40px;
    }

    .date-nav-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: none;
      border: none;
      color: #737373;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: color 0.2s ease;
    }

    .date-nav-btn:hover {
      color: #000000;
    }

    .arrow-icon {
      width: 20px;
      height: 15px;
    }

    .current-date {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .date-display {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 20px;
      line-height: 1.21;
      color: #000000;
    }

    .calendar-icon {
      width: 25px;
      height: 25px;
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

    .attendance-table-container {
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

    .staff-name {
      text-align: center;
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

      .date-navigation {
        flex-direction: column;
        gap: 20px;
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
    }
  </style>

  <script>
    function changeDate(direction) {
      const currentDate = new Date('{{ $date->format('Y-m-d') }}');
      currentDate.setDate(currentDate.getDate() + direction);

      const newDate = currentDate.toISOString().split('T')[0];
      window.location.href = `{{ route('admin.attendance.daily') }}?date=${newDate}`;
    }
  </script>
@endsection
