@extends('layouts.app')

@section('title', '申請一覧（管理者）')

@section('content')
  <div class="admin-modification-requests-container">
    <!-- ヘッダー -->
    <x-admin.header active-page="requests" />

    <!-- メインコンテンツ -->
    <div class="admin-content">
      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>申請一覧</h1>
      </div>

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
        <div class="requests-table-container">
          @if (count($pendingRequests) > 0)
            <table class="requests-table">
              <thead>
                <tr class="table-header">
                  <th>状態</th>
                  <th>名前</th>
                  <th>対象日時</th>
                  <th>申請理由</th>
                  <th>申請日時</th>
                  <th>詳細</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($pendingRequests as $request)
                  <tr class="table-row">
                    <td class="status-cell">
                      <span class="status-badge status-pending">承認待ち</span>
                    </td>
                    <td class="name-cell">{{ $request['user']['name'] }}</td>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('Y/m/d') }}
                    </td>
                    <td class="reason-cell">{{ Str::limit($request['requested_remarks'], 20) }}</td>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($request['created_at'])->format('Y/m/d') }}</td>
                    <td class="detail-cell">
                      <a href="{{ route('admin.attendance.detail', $request['attendance_id']) }}"
                        class="detail-link">詳細</a>
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
      </div>

      <!-- 承認済みタブ -->
      <div id="approved-tab" class="tab-content">
        <div class="requests-table-container">
          @if (count($approvedRequests) > 0)
            <table class="requests-table">
              <thead>
                <tr class="table-header">
                  <th>状態</th>
                  <th>名前</th>
                  <th>対象日時</th>
                  <th>申請理由</th>
                  <th>申請日時</th>
                  <th>詳細</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($approvedRequests as $request)
                  <tr class="table-row">
                    <td class="status-cell">
                      <span class="status-badge status-approved">承認済み</span>
                    </td>
                    <td class="name-cell">{{ $request['user']['name'] }}</td>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('Y/m/d') }}
                    </td>
                    <td class="reason-cell">{{ Str::limit($request['requested_remarks'], 20) }}</td>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($request['created_at'])->format('Y/m/d') }}</td>
                    <td class="detail-cell">
                      <a href="{{ route('admin.attendance.detail', $request['attendance_id']) }}"
                        class="detail-link">詳細</a>
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
      </div>
    </div>
  </div>

  <style>
    .admin-modification-requests-container {
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

    .tab-navigation {
      display: flex;
      gap: 40px;
      margin-bottom: 40px;
      border-bottom: 1px solid #000000;
      padding-bottom: 10px;
    }

    .tab-button {
      background: none;
      border: none;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #000000;
      padding: 0;
      transition: opacity 0.2s ease;
    }

    .tab-button:hover {
      opacity: 0.7;
    }

    .tab-button.active {
      color: #000000;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .requests-table-container {
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .requests-table {
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

    .status-cell {
      text-align: left;
    }

    .name-cell {
      text-align: left;
    }

    .date-cell {
      text-align: left;
    }

    .reason-cell {
      text-align: left;
    }

    .detail-link {
      color: #000000;
      text-decoration: none;
      transition: opacity 0.2s ease;
    }

    .detail-link:hover {
      opacity: 0.7;
    }

    .status-badge {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 14px;
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

    .empty-message {
      text-align: center;
      padding: 40px;
      color: #737373;
      font-style: italic;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
    }

    .message {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      text-align: center;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
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

      .tab-navigation {
        gap: 20px;
      }

      .requests-table-container {
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
