@extends('layouts.app')

@section('title', '申請詳細')

@section('content')
  <div class="modification-request-detail-container">
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

      @if (session('error'))
        <div class="message message-error">
          {{ session('error') }}
        </div>
      @endif

      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>勤怠詳細</h1>
      </div>

      <!-- 申請詳細カード -->
      <div class="modification-request-detail-card">
        <!-- 申請者名 -->
        <div class="detail-row">
          <div class="detail-label">名前</div>
          <div class="detail-value">{{ $modificationRequest->user->name }}</div>
        </div>

        <!-- 対象日付 -->
        <div class="detail-row">
          <div class="detail-label">日付</div>
          <div class="detail-value">
            <span class="year">{{ \Carbon\Carbon::parse($modificationRequest->attendance->date)->format('Y年') }}</span>
            <span
              class="date">{{ \Carbon\Carbon::parse($modificationRequest->attendance->date)->format('m月d日') }}</span>
          </div>
        </div>

        <!-- 申請した出勤・退勤時間 -->
        <div class="detail-row">
          <div class="detail-label">出勤・退勤</div>
          <div class="detail-value">
            <span
              class="time-display requested">{{ $modificationRequest->requested_start_time ? \Carbon\Carbon::parse($modificationRequest->requested_start_time)->format('H:i') : '--:--' }}</span>
            <span class="separator">〜</span>
            <span
              class="time-display requested">{{ $modificationRequest->requested_end_time ? \Carbon\Carbon::parse($modificationRequest->requested_end_time)->format('H:i') : '--:--' }}</span>
          </div>
        </div>

        <!-- 申請した休憩時間 -->
        @foreach ($modificationRequest->modificationRequestBreaks as $index => $break)
          <div class="detail-row">
            <div class="detail-label">休憩{{ $index + 1 }}</div>
            <div class="detail-value">
              @if ($modificationRequest->modificationRequestBreaks->count() > 0)
                <div class="break-time">
                  <span
                    class="time-display requested">{{ $break->requested_start_time ? \Carbon\Carbon::parse($break->requested_start_time)->format('H:i') : '--:--' }}</span>
                  <span class="separator">〜</span>
                  <span
                    class="time-display requested">{{ $break->requested_end_time ? \Carbon\Carbon::parse($break->requested_end_time)->format('H:i') : '--:--' }}</span>
                </div>
              @else
                <span class="time-display">--:--</span>
                <span class="separator">〜</span>
                <span class="time-display">--:--</span>
              @endif
            </div>
          </div>
        @endforeach

        <!-- 備考 -->
        <div class="detail-row">
          <div class="detail-label">備考</div>
          <div class="detail-value">{{ $modificationRequest->requested_remarks ?: '遅延のため' }}</div>
        </div>

        @if ($modificationRequest->status === 'approved')
          <!-- 承認者 -->
          <div class="detail-row">
            <div class="detail-label">承認者</div>
            <div class="detail-value">
              {{ $modificationRequest->approved_by ? \App\Models\User::find($modificationRequest->approved_by)->name : 'システム' }}
            </div>
          </div>

          <!-- 承認日時 -->
          <div class="detail-row">
            <div class="detail-label">承認日時</div>
            <div class="detail-value">
              {{ $modificationRequest->approved_at ? \Carbon\Carbon::parse($modificationRequest->approved_at)->format('Y年m月d日 H:i') : '--' }}
            </div>
          </div>
        @endif
      </div>

      <!-- 注意メッセージ -->
      @if ($modificationRequest->status === 'pending')
        <div class="notice-message">
          *承認待ちのため修正はできません。
        </div>
      @endif
    </div>
  </div>

  <style>
    .modification-request-detail-container {
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
      background: none;
      border: none;
      cursor: pointer;
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

    .modification-request-detail-card {
      background-color: #FFFFFF;
      border-radius: 10px;
      border: 1px solid #E1E1E1;
      padding: 40px;
      margin-bottom: 40px;
    }

    .detail-row {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #E1E1E1;
    }

    .detail-row:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .detail-label {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
      width: 200px;
      flex-shrink: 0;
    }

    .detail-value {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #000000;
      flex: 1;
    }

    .year {
      font-size: 16px;
      color: #000000;
      margin-right: 8px;
    }

    .date {
      font-size: 16px;
      color: #000000;
    }

    .time-display {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #000000;
    }

    .time-display.requested {
      color: #000000;
    }

    .separator {
      margin: 0 10px;
      color: #000000;
    }

    .break-time {
      margin-bottom: 8px;
    }

    .break-time:last-child {
      margin-bottom: 0;
    }

    .status-badge {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      padding: 8px 16px;
      border-radius: 20px;
      display: inline-block;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-approved {
      background-color: #d4edda;
      color: #155724;
    }

    .status-rejected {
      background-color: #f8d7da;
      color: #721c24;
    }

    .notice-message {
      font-family: 'Inter', sans-serif;
      font-weight: 800;
      font-size: 18px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: rgba(255, 0, 0, 0.5);
      text-align: right;
      margin-bottom: 20px;
    }

    .button-area {
      display: flex;
      gap: 20px;
      justify-content: center;
    }

    .back-button,
    .attendance-detail-button {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      color: #FFFFFF;
      background-color: #000000;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      transition: opacity 0.2s ease;
      display: inline-block;
    }

    .back-button:hover,
    .attendance-detail-button:hover {
      opacity: 0.8;
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

      .modification-request-detail-card {
        padding: 20px;
      }

      .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .detail-label {
        width: auto;
        font-size: 14px;
      }

      .detail-value {
        font-size: 14px;
      }

      .notice-message {
        font-size: 16px;
        text-align: left;
      }

      .button-area {
        flex-direction: column;
        align-items: center;
      }

      .back-button,
      .attendance-detail-button {
        width: 100%;
        max-width: 300px;
        text-align: center;
      }
    }

    @media (max-width: 480px) {
      .page-title h1 {
        font-size: 20px;
      }

      .detail-label,
      .detail-value {
        font-size: 12px;
      }

      .notice-message {
        font-size: 14px;
      }
    }
  </style>
@endsection
