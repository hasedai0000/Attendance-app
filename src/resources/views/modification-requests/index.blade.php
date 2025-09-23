@extends('layouts.app')

@section('title', '修正申請一覧')

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
    }

    .detail-btn:hover {
      background-color: #138496;
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
  </style>
@endsection

@section('content')
  <div class="modification-requests-container">
    <h1>修正申請一覧</h1>

    @if (session('message'))
      <div class="message message-success">
        {{ session('message') }}
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
              <th>申請日</th>
              <th>勤怠日</th>
              <th>出勤時刻</th>
              <th>退勤時刻</th>
              <th>備考</th>
              <th>ステータス</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pendingRequests as $request)
              <tr>
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
                <td><a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-btn">詳細</a></td>
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
                <td><a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-btn">詳細</a></td>
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
      <a href="{{ route('attendance.index') }}">勤怠打刻に戻る</a>
      <a href="{{ route('attendance.list') }}">勤怠一覧</a>
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
