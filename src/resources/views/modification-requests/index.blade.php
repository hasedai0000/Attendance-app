@extends('layouts.app')

@section('title', '修正申請一覧')

@section('content')
  <div class="modification-requests-container">
    <!-- ヘッダー -->
    <div class="attendance-header">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
      <nav class="attendance-nav">
        <a href="{{ route('attendance.index') }}" class="nav-item">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="nav-item">勤怠一覧</a>
        <a href="{{ route('modification-requests.index') }}" class="nav-item active">申請</a>
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

      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>申請一覧</h1>
      </div>

      <!-- タブナビゲーション -->
      <div class="tab-navigation">
        <button class="tab-button active" onclick="showTab('pending')">承認待ち</button>
        <button class="tab-button" onclick="showTab('approved')">承認済み</button>
      </div>

      <!-- 承認待ちタブ -->
      <div id="pendingTab" class="tab-content active">
        <div class="requests-card">
          <div class="table-header">
            <div class="header-cell">状態</div>
            <div class="header-cell">名前</div>
            <div class="header-cell">対象日時</div>
            <div class="header-cell">申請理由</div>
            <div class="header-cell">申請日時</div>
            <div class="header-cell">詳細</div>
          </div>

          @if (count($pendingRequests) > 0)
            @foreach ($pendingRequests as $request)
              <div class="table-row">
                <div class="table-cell">
                  <span class="status-badge status-pending">承認待ち</span>
                </div>
                <div class="table-cell">{{ $request['attendance']['user']['name'] ?? '西伶奈' }}</div>
                <div class="table-cell">{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('Y/m/d') }}</div>
                <div class="table-cell">{{ Str::limit($request['requested_remarks'], 20) ?: '遅延のため' }}</div>
                <div class="table-cell">{{ \Carbon\Carbon::parse($request['created_at'])->format('Y/m/d') }}</div>
                <div class="table-cell">
                  <a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-link">詳細</a>
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-message">
              承認待ちの修正申請はありません。
            </div>
          @endif
        </div>
      </div>

      <!-- 承認済みタブ -->
      <div id="approvedTab" class="tab-content">
        <div class="requests-card">
          <div class="table-header">
            <div class="header-cell">状態</div>
            <div class="header-cell">名前</div>
            <div class="header-cell">対象日時</div>
            <div class="header-cell">申請理由</div>
            <div class="header-cell">申請日時</div>
            <div class="header-cell">詳細</div>
          </div>

          @if (count($approvedRequests) > 0)
            @foreach ($approvedRequests as $request)
              <div class="table-row">
                <div class="table-cell">
                  <span class="status-badge status-approved">承認済み</span>
                </div>
                <div class="table-cell">{{ $request['attendance']['user']['name'] ?? '西伶奈' }}</div>
                <div class="table-cell">{{ \Carbon\Carbon::parse($request['attendance']['date'])->format('Y/m/d') }}
                </div>
                <div class="table-cell">{{ Str::limit($request['requested_remarks'], 20) ?: '遅延のため' }}</div>
                <div class="table-cell">{{ \Carbon\Carbon::parse($request['created_at'])->format('Y/m/d') }}</div>
                <div class="table-cell">
                  <a href="{{ route('attendance.detail', $request['attendance_id']) }}" class="detail-link">詳細</a>
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-message">
              承認済みの修正申請はありません。
            </div>
          @endif
        </div>
      </div>
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
      document.getElementById(tabName + 'Tab').classList.add('active');
    }
  </script>

  <style>
    .modification-requests-container {
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

    .tab-navigation {
      display: flex;
      gap: 0;
      margin-bottom: 40px;
      border-bottom: 1px solid #000000;
    }

    .tab-button {
      padding: 0;
      background: none;
      border: none;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #000000;
      margin-right: 40px;
      padding-bottom: 8px;
      transition: opacity 0.2s ease;
    }

    .tab-button.active {
      color: #000000;
    }

    .tab-button:hover {
      opacity: 0.8;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .requests-card {
      background-color: #FFFFFF;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .table-header {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
      background-color: #FFFFFF;
      border-bottom: 3px solid #E1E1E1;
      padding: 14px 0;
    }

    .header-cell {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #737373;
      text-align: left;
      padding: 0 37px;
    }

    .table-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
      border-bottom: 2px solid #E1E1E1;
      padding: 14px 0;
      align-items: center;
    }

    .table-row:last-child {
      border-bottom: none;
    }

    .table-cell {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #737373;
      text-align: left;
      padding: 0 37px;
    }

    .status-badge {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #737373;
    }

    .status-pending {
      color: #737373;
    }

    .status-approved {
      color: #737373;
    }

    .detail-link {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #000000;
      text-decoration: none;
      transition: opacity 0.2s ease;
    }

    .detail-link:hover {
      opacity: 0.8;
    }

    .empty-message {
      text-align: center;
      padding: 40px;
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
      .table-row {
        grid-template-columns: 1fr;
        gap: 8px;
      }

      .header-cell,
      .table-cell {
        padding: 8px 16px;
        border-bottom: 1px solid #E1E1E1;
      }

      .header-cell:before,
      .table-cell:before {
        content: attr(data-label) ": ";
        font-weight: 700;
        color: #737373;
      }

      .table-header {
        display: none;
      }
    }

    @media (max-width: 480px) {
      .page-title h1 {
        font-size: 20px;
      }

      .tab-button {
        font-size: 14px;
        margin-right: 20px;
      }

      .header-cell,
      .table-cell {
        font-size: 14px;
      }
    }
  </style>
@endsection
