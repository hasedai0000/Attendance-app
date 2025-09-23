@extends('layouts.app')

@section('title', 'スタッフ勤怠一覧')

@section('css')
  <style>
    .staff-attendance-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .staff-info {
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      text-align: center;
    }

    .staff-name {
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 0.5rem;
    }

    .staff-email {
      color: #666;
    }

    .month-navigation {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 2rem;
      gap: 1rem;
    }

    .month-navigation a {
      padding: 0.5rem 1rem;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }

    .month-navigation a:hover {
      background-color: #0056b3;
    }

    .current-month {
      font-size: 1.5rem;
      font-weight: bold;
      padding: 0 1rem;
    }

    .attendance-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .attendance-table th,
    .attendance-table td {
      padding: 0.75rem;
      text-align: center;
      border: 1px solid #ddd;
    }

    .attendance-table th {
      background-color: #f8f9fa;
      font-weight: bold;
    }

    .attendance-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .attendance-table tr:hover {
      background-color: #e8f4f8;
    }

    .detail-btn {
      padding: 0.25rem 0.75rem;
      background-color: #17a2b8;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.875rem;
    }

    .detail-btn:hover {
      background-color: #138496;
    }

    .csv-btn {
      padding: 0.5rem 1rem;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.875rem;
      margin-bottom: 1rem;
      display: inline-block;
    }

    .csv-btn:hover {
      background-color: #218838;
    }

    .nav-links {
      text-align: center;
      margin-top: 2rem;
    }

    .nav-links a {
      display: inline-block;
      padding: 0.5rem 1rem;
      margin: 0 0.5rem;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }

    .nav-links a:hover {
      background-color: #0056b3;
    }

    .empty-message {
      text-align: center;
      padding: 2rem;
      color: #666;
      font-style: italic;
    }
  </style>
@endsection

@section('content')
  <div class="staff-attendance-container">
    <h1>スタッフ勤怠一覧</h1>

    <!-- スタッフ情報 -->
    <div class="staff-info">
      <div class="staff-name">{{ $user['name'] }}</div>
      <div class="staff-email">{{ $user['email'] }}</div>
    </div>

    <!-- CSV出力ボタン -->
    <div style="text-align: center;">
      <a href="{{ route('admin.staff.attendance.csv', ['userId' => $user['id'], 'month' => $month]) }}"
        class="csv-btn">CSV出力</a>
    </div>

    <!-- 月間ナビゲーション -->
    <div class="month-navigation">
      @php
        $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $month);
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
      @endphp

      <a
        href="{{ route('admin.staff.attendance', ['userId' => $user['id'], 'month' => $prevMonth->format('Y-m')]) }}">前月</a>
      <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
      <a
        href="{{ route('admin.staff.attendance', ['userId' => $user['id'], 'month' => $nextMonth->format('Y-m')]) }}">翌月</a>
    </div>

    <!-- 勤怠一覧テーブル -->
    @if (count($attendances) > 0)
      <table class="attendance-table">
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤時刻</th>
            <th>退勤時刻</th>
            <th>休憩時間</th>
            <th>勤務時間</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($attendances as $attendance)
            <tr>
              <td>{{ \Carbon\Carbon::parse($attendance['date'])->format('m/d') }}</td>
              <td>{{ $attendance['start_time'] ? \Carbon\Carbon::parse($attendance['start_time'])->format('H:i') : '' }}
              </td>
              <td>{{ $attendance['end_time'] ? \Carbon\Carbon::parse($attendance['end_time'])->format('H:i') : '' }}</td>
              <td>
                @if (isset($attendance['breaks']) && count($attendance['breaks']) > 0)
                  @php
                    $totalBreakMinutes = 0;
                    foreach ($attendance['breaks'] as $break) {
                        if ($break['start_time'] && $break['end_time']) {
                            $start = \Carbon\Carbon::parse($break['start_time']);
                            $end = \Carbon\Carbon::parse($break['end_time']);
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
              <td>
                @if ($attendance['start_time'] && $attendance['end_time'])
                  @php
                    $start = \Carbon\Carbon::parse($attendance['start_time']);
                    $end = \Carbon\Carbon::parse($attendance['end_time']);
                    $workMinutes = $start->diffInMinutes($end);
                    // 休憩時間を引く
                    if (isset($attendance['breaks'])) {
                        foreach ($attendance['breaks'] as $break) {
                            if ($break['start_time'] && $break['end_time']) {
                                $breakStart = \Carbon\Carbon::parse($break['start_time']);
                                $breakEnd = \Carbon\Carbon::parse($break['end_time']);
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
              <td>
                <a href="{{ route('attendance.detail', $attendance['id']) }}" class="detail-btn">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty-message">
        この月の勤怠データはありません。
      </div>
    @endif

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('admin.staff.list') }}">スタッフ一覧に戻る</a>
      <a href="{{ route('admin.index') }}">管理者ダッシュボード</a>
    </div>
  </div>
@endsection
```

</rewritten_file>
