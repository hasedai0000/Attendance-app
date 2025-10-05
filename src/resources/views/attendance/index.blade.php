@extends('layouts.app')

@section('title', '勤怠打刻')

@section('content')
  <div class="attendance-container">
    <!-- ヘッダー -->
    <div class="attendance-header">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
      <nav class="attendance-nav">
        <a href="{{ route('attendance.index') }}" class="nav-item active">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="nav-item">勤怠一覧</a>
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

      <!-- ステータスバッジ -->
      <div class="status-badge">
        @switch($currentStatus)
          @case('not_working')
            <span class="status-text">勤務外</span>
          @break

          @case('working')
            <span class="status-text">出勤中</span>
          @break

          @case('on_break')
            <span class="status-text">休憩中</span>
          @break

          @case('finished')
            <span class="status-text">退勤済</span>
          @break

          @default
            <span class="status-text">不明</span>
        @endswitch
      </div>

      <!-- 日付表示 -->
      <div class="date-display">
        {{ $currentDateTime->format('Y年m月d日') }}
      </div>

      <!-- 時刻表示 -->
      <div class="time-display">
        {{ $currentDateTime->format('H:i') }}
      </div>

      <!-- アクションボタン -->
      <div class="action-buttons">
        @if ($currentStatus === 'not_working')
          <form action="{{ route('attendance.start-work') }}" method="POST">
            @csrf
            <button type="submit" class="action-btn btn-start-work">出勤</button>
          </form>
        @endif

        @if ($currentStatus === 'working')
          <div class="working-buttons">
            <form action="{{ route('attendance.end-work') }}" method="POST" class="end-work-form">
              @csrf
              <button type="submit" class="action-btn btn-end-work">退勤</button>
            </form>

            <form action="{{ route('attendance.start-break') }}" method="POST" class="break-form">
              @csrf
              <button type="submit" class="action-btn btn-start-break">休憩入</button>
            </form>
          </div>
        @endif

        @if ($currentStatus === 'on_break')
          <form action="{{ route('attendance.end-break') }}" method="POST">
            @csrf
            <button type="submit" class="action-btn btn-end-break">休憩戻</button>
          </form>
        @endif

        @if ($currentStatus === 'finished')
          <div class="finished-message">
            <p>お疲れ様でした。</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .attendance-container {
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
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      max-width: 1512px;
      margin: 0 auto;
      width: 100%;
    }

    .date-display {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 40px;
      line-height: 1.21;
      color: #000000;
      margin-bottom: 40px;
      text-align: center;
    }

    .time-display {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 80px;
      line-height: 1.21;
      color: #000000;
      margin-bottom: 40px;
      text-align: center;
    }

    .status-badge {
      background-color: #C8C8C8;
      border-radius: 50px;
      padding: 8px 20px;
      margin-bottom: 40px;
    }

    .status-text {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 18px;
      line-height: 1.21;
      color: #696969;
      letter-spacing: 0.15em;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
    }

    .working-buttons {
      display: flex;
      gap: 20px;
      justify-content: center;
    }

    .working-buttons form {
      display: inline-block;
    }

    .action-btn {
      width: 221px;
      height: 77px;
      border: none;
      border-radius: 20px;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 32px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: opacity 0.2s ease;
      text-align: center;
    }

    .btn-start-work {
      background-color: #000000;
      color: #FFFFFF;
    }

    .btn-start-work:hover {
      opacity: 0.8;
    }

    .btn-start-break {
      background-color: #ffffff;
      color: #000000;
    }

    .btn-start-break:hover {
      opacity: 0.8;
    }

    .btn-end-break {
      background-color: #FFFFFF;
      color: #000000;
    }

    .btn-end-break:hover {
      opacity: 0.8;
    }

    .btn-end-work {
      background-color: #000000;
      color: #ffffff;
    }

    .btn-end-work:hover {
      opacity: 0.8;
    }

    .finished-message {
      text-align: center;
    }

    .finished-message p {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 26px;
      line-height: 1.21;
      color: #000000;
      letter-spacing: 0.15em;
      margin: 0;
    }

    .message {
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 4px;
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

      .date-display {
        font-size: 32px;
        margin-bottom: 30px;
      }

      .time-display {
        font-size: 60px;
        margin-bottom: 30px;
      }

      .action-btn {
        width: 200px;
        height: 60px;
        font-size: 28px;
      }

      .working-buttons {
        flex-direction: column;
        gap: 15px;
      }

      .finished-message p {
        font-size: 22px;
      }
    }

    @media (max-width: 480px) {
      .date-display {
        font-size: 28px;
      }

      .time-display {
        font-size: 48px;
      }

      .action-btn {
        width: 180px;
        height: 50px;
        font-size: 24px;
      }

      .working-buttons {
        flex-direction: column;
        gap: 12px;
      }

      .finished-message p {
        font-size: 20px;
      }
    }
  </style>

  <script>
    // 現在時刻を1分ごとに更新
    function updateTime() {
      const now = new Date();
      const timeElement = document.querySelector('.time-display');
      if (timeElement) {
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}`;
      }
    }

    // ページ読み込み時に時刻更新を開始
    document.addEventListener('DOMContentLoaded', function() {
      updateTime();
      setInterval(updateTime, 60000); // 1分ごとに更新
    });
  </script>
@endsection
