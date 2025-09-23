@extends('layouts.app')

@section('title', '日次勤怠一覧')

@section('css')
  <style>
    .daily-attendance-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .date-navigation {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 2rem;
      gap: 1rem;
    }

    .date-navigation a {
      padding: 0.5rem 1rem;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }

    .date-navigation a:hover {
      background-color: #0056b3;
    }

    .current-date {
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

    .status-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.875rem;
      font-weight: bold;
    }

    .status-not-working {
      background-color: #f8d7da;
      color: #721c24;
    }

    .status-working {
      background-color: #d4edda;
      color: #155724;
    }

    .status-on-break {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-finished {
      background-color: #d1ecf1;
      color: #0c5460;
    }
  </style>
@endsection

@section('content')
  <div class="daily-attendance-container">
    <h1>日次勤怠一覧</h1>

    <!-- 日付ナビゲーション -->
    <div class="date-navigation">
      @php
        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();
      @endphp

      <a href="{{ route('admin.attendance.daily', ['date' => $prevDate->format('Y-m-d')]) }}">前日</a>
      <span class="current-date">{{ $date->format('Y年m月d日') }}</span>
      <a href="{{ route('admin.attendance.daily', ['date' => $nextDate->format('Y-m-d')]) }}">翌日</a>
    </div>

    <!-- 勤怠一覧テーブル -->
    @if (count($attendances) > 0)
      <table class="attendance-table">
        <thead>
          <tr>
            <th>スタッフ名</th>
            <th>出勤時刻</th>
            <th>退勤時刻</th>
            <th>休憩時間</th>
            <th>勤務時間</th>
            <th>ステータス</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($attendances as $attendance)
            <tr>
              <td>{{ $attendance['user']['name'] }}</td>
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
                <span class="status-badge status-{{ $attendance['status'] }}">
                  @switch($attendance['status'])
                    @case('not_working')
                      勤務外
                    @break

                    @case('working')
                      出勤中
                    @break

                    @case('on_break')
                      休憩中
                    @break

                    @case('finished')
                      退勤済
                    @break

                    @default
                      不明
                  @endswitch
                </span>
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
        この日の勤怠データはありません。
      </div>
    @endif

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('admin.index') }}">管理者ダッシュボードに戻る</a>
      <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
    </div>
  </div>
@endsection
```

スタッフ一覧画面を作成します。

```blade:src/resources/views/admin/staff-list.blade.php
@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
  <style>
    .staff-list-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .staff-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .staff-table th,
    .staff-table td {
      padding: 0.75rem;
      text-align: center;
      border: 1px solid #ddd;
    }

    .staff-table th {
      background-color: #f8f9fa;
      font-weight: bold;
    }

    .staff-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .staff-table tr:hover {
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
  <div class="staff-list-container">
    <h1>スタッフ一覧</h1>

    @if (count($staff) > 0)
      <table class="staff-table">
        <thead>
          <tr>
            <th>氏名</th>
            <th>メールアドレス</th>
            <th>登録日</th>
            <th>勤怠詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($staff as $user)
            <tr>
              <td>{{ $user['name'] }}</td>
              <td>{{ $user['email'] }}</td>
              <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('Y/m/d') }}</td>
              <td>
                <a href="{{ route('admin.staff.attendance', $user['id']) }}" class="detail-btn">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty-message">
        スタッフデータがありません。
      </div>
    @endif

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('admin.index') }}">管理者ダッシュボードに戻る</a>
      <a href="{{ route('admin.attendance.daily') }}">日次勤怠一覧</a>
    </div>
  </div>
@endsection
```

スタッフ月次勤怠一覧画面を作成します。

```blade:src/resources/views/admin/staff-attendance.blade.php
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

最後に、管理者用の修正申請一覧画面を作成します。

```blade:src/resources/views/admin/modification-requests.blade.php
@extends('layouts.app')

@section('title', '修正申請一覧（管理者）')

@section('css')
  <style>
    .modification-requests-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .tab-navigation {
      display: flex;
      margin-bottom: 2rem;
      border-bottom: 2px solid #dee2e6;
    }

    .tab-button {
      padding: 1rem 2rem;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      color: #6c757d;
      border-bottom: 2px solid transparent;
      transition: all 0.3s;
    }

    .tab-button.active {
      color: #007bff;
      border-bottom-color: #007bff;
    }

    .tab-button:hover {
      color: #007bff;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .requests-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .requests-table th,
    .requests-table td {
      padding: 0.75rem;
      text-align: center;
      border: 1px solid #ddd;
    }

    .requests-table th {
      background-color: #f8f9fa;
      font-weight: bold;
    }

    .requests-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .requests-table tr:hover {
      background-color: #e8f4f8;
    }

    .detail-btn {
      padding: 0.25rem 0.75rem;
      background-color: #17a2b8;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.875rem;
      margin-right: 0.5rem;
    }

    .detail-btn:hover {
      background-color: #138496;
    }

    .approve-btn {
      padding: 0.25rem 0.75rem;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.875rem;
      border: none;
      cursor: pointer;
    }

    .approve-btn:hover {
      background-color: #218838;
    }

    .status-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.875rem;
      font-weight: bold;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-approved {
      background-color: #d4edda;
      color: #155724;
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

    .message {
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 4px;
      text-align: center;
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
  </style>
@endsection

@section('content')
  <div class="modification-requests-container">
    <h1>修正申請一覧（管理者）</h1>

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

    <!-- タブナビゲーション -->
    <div class="tab-navigation">
      <button class="tab-button active" onclick="showTab('pending')">承認待ち</button>
      <button class="tab-button" onclick="showTab('approved')">承認済み</button>
    </div>

    <!-- 承認待ちタブ -->
    <div id="pending-tab" class="tab-content active">
      <h2>承認待ち</h2>
      @if (count($pendingRequests) > 0)
        <table class="requests-table">
          <thead>
            <tr>
              <th>申請者</th>
              <th>申請日</th>
              <th>勤怠日</th>
              <th>出勤時刻</th>
              <th>退勤時刻</th>
              <th>備考</th>
              <th>ステータス</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pendingRequests as $request)
              <tr>
                <td>{{ $request['user']['name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($request['created_at'])->format('m/d H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('m/d') }}</td>
                <td>
                  {{ $request['requested_start_time'] ? \Carbon\Carbon::parse($request['requested_start_time'])->format('H:i') : '-' }}
                </td>
                <td>
                  {{ $request['requested_end_time'] ? \Carbon\Carbon::parse($request['requested_end_time'])->format('H:i') : '-' }}
                </td>
                <td>{{ Str::limit($request['requested_remarks'], 30) }}</td>
                <td><span class="status-badge status-pending">承認待ち</span></td>
                <td>
                  <a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-btn">詳細</a>
                  <form action="{{ route('admin.modification-requests.approve', $request['id']) }}" method="POST"
                    style="display: inline;">
                    @csrf
                    <button type="submit" class="approve-btn" onclick="return confirm('この修正申請を承認しますか？')">承認</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="empty-message">
          承認待ちの修正申請はありません。
        </div>
      @endif
    </div>

    <!-- 承認済みタブ -->
    <div id="approved-tab" class="tab-content">
      <h2>承認済み</h2>
      @if (count($approvedRequests) > 0)
        <table class="requests-table">
          <thead>
            <tr>
              <th>申請者</th>
              <th>申請日</th>
              <th>勤怠日</th>
              <th>出勤時刻</th>
              <th>退勤時刻</th>
              <th>備考</th>
              <th>ステータス</th>
              <th>承認日</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($approvedRequests as $request)
              <tr>
                <td>{{ $request['user']['name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($request['created_at'])->format('m/d H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('m/d') }}</td>
                <td>
                  {{ $request['requested_start_time'] ? \Carbon\Carbon::parse($request['requested_start_time'])->format('H:i') : '-' }}
                </td>
                <td>
                  {{ $request['requested_end_time'] ? \Carbon\Carbon::parse($request['requested_end_time'])->format('H:i') : '-' }}
                </td>
                <td>{{ Str::limit($request['requested_remarks'], 30) }}</td>
                <td><span class="status-badge status-approved">承認済み</span></td>
                <td>
                  {{ $request['approved_at'] ? \Carbon\Carbon::parse($request['approved_at'])->format('m/d H:i') : '-' }}
                </td>
                <td>
                  <a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-btn">詳細</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="empty-message">
          承認済みの修正申請はありません。
        </div>
      @endif
    </div>

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('admin.index') }}">管理者ダッシュボードに戻る</a>
      <a href="{{ route('admin.attendance.daily') }}">日次勤怠一覧</a>
    </div>
  </div>

  <script>
    function showTab(tabName) {
      // すべてのタブボタンからactiveクラスを削除
      document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
      });

      // すべてのタブコンテンツを非表示
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });

      // クリックされたタブボタンにactiveクラスを追加
      event.target.classList.add('active');

      // 対応するタブコンテンツを表示
      document.getElementById(tabName + '-tab').classList.add('active');
    }
  </script>
@endsection
```

</rewritten_file>
