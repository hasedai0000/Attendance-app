@extends('layouts.app')

@section('title', '勤怠打刻')

@section('css')
  <style>
    .attendance-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 2rem;
    }

    .datetime-display {
      text-align: center;
      margin-bottom: 2rem;
      padding: 1rem;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .date {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .time {
      font-size: 1.2rem;
      color: #666;
    }

    .status-display {
      text-align: center;
      margin-bottom: 2rem;
      padding: 1rem;
      border-radius: 8px;
    }

    .status-working {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .status-on-break {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .status-finished {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    .status-not-working {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      max-width: 300px;
      margin: 0 auto;
    }

    .action-btn {
      padding: 1rem 2rem;
      font-size: 1.1rem;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .btn-start-work {
      background-color: #28a745;
      color: white;
    }

    .btn-start-work:hover {
      background-color: #218838;
    }

    .btn-start-break {
      background-color: #ffc107;
      color: #212529;
    }

    .btn-start-break:hover {
      background-color: #e0a800;
    }

    .btn-end-break {
      background-color: #17a2b8;
      color: white;
    }

    .btn-end-break:hover {
      background-color: #138496;
    }

    .btn-end-work {
      background-color: #dc3545;
      color: white;
    }

    .btn-end-work:hover {
      background-color: #c82333;
    }

    .btn-disabled {
      background-color: #6c757d;
      color: white;
      cursor: not-allowed;
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
  </style>
@endsection

@section('content')
  <div class="attendance-container">
    <h1>勤怠打刻</h1>

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

    <!-- 現在の日時表示 -->
    <div class="datetime-display">
      <div class="date">{{ $currentDateTime->format('Y年m月d日') }}</div>
      <div class="time">{{ $currentDateTime->format('H:i:s') }}</div>
    </div>

    <!-- ステータス表示 -->
    <div class="status-display status-{{ $currentStatus }}">
      <h2>現在のステータス:
        @switch($currentStatus)
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
      </h2>
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
        <form action="{{ route('attendance.start-break') }}" method="POST">
          @csrf
          <button type="submit" class="action-btn btn-start-break">休憩入</button>
        </form>

        <form action="{{ route('attendance.end-work') }}" method="POST">
          @csrf
          <button type="submit" class="action-btn btn-end-work">退勤</button>
        </form>
      @endif

      @if ($currentStatus === 'on_break')
        <form action="{{ route('attendance.end-break') }}" method="POST">
          @csrf
          <button type="submit" class="action-btn btn-end-break">休憩戻</button>
        </form>
      @endif

      @if ($currentStatus === 'finished')
        <p>本日の勤務は終了しています。お疲れ様でした。</p>
      @endif
    </div>

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('attendance.list') }}">勤怠一覧</a>
    </div>
  </div>

  <script>
    // 現在時刻を1秒ごとに更新
    function updateTime() {
      const now = new Date();
      const timeElement = document.querySelector('.time');
      if (timeElement) {
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}:${seconds}`;
      }
    }

    // ページ読み込み時に時刻更新を開始
    document.addEventListener('DOMContentLoaded', function() {
      updateTime();
      setInterval(updateTime, 1000);
    });
  </script>
@endsection
