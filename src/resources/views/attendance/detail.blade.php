@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
  <div class="attendance-detail-container">
    <!-- ヘッダー -->
    <div class="attendance-header">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
      <nav class="attendance-nav">
        <a href="{{ route('attendance.index') }}" class="nav-item">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="nav-item active">勤怠一覧</a>
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

      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>勤怠詳細</h1>
      </div>

      <!-- 勤怠詳細カード -->
      <div class="attendance-detail-card">
        <!-- 名前 -->
        <div class="detail-row">
          <div class="detail-label">名前</div>
          <div class="detail-value">{{ $attendance->user->name }}</div>
        </div>

        <!-- 日付 -->
        <div class="detail-row">
          <div class="detail-label">日付</div>
          <div class="detail-value">
            <span class="year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
            <span class="date">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</span>
          </div>
        </div>

        <form action="{{ route('modification-requests.store') }}" method="POST">
          @csrf
          <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
          <!-- 出勤・退勤 -->
          <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value">
              <input class="time-display" type="time" name="start_time"
                value="{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '--:--' }}">
              <span class="separator">〜</span>
              <input class="time-display" type="time" name="end_time"
                value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '--:--' }}">
            </div>
          </div>

          <!-- 休憩時間 -->
          @if (count($breaks) > 0)
            @foreach ($breaks as $index => $break)
              <div class="detail-row">
                <div class="detail-label">休憩{{ $index + 1 }}</div>
                <div class="detail-value">
                  <input class="time-display" type="time" name="breaks[{{ $index }}][start_time]"
                    value="{{ $break['start_time'] ? \Carbon\Carbon::parse($break['start_time'])->format('H:i') : '--:--' }}">
                  <span class="separator">〜</span>
                  <input class="time-display" type="time" name="breaks[{{ $index }}][end_time]"
                    value="{{ $break['end_time'] ? \Carbon\Carbon::parse($break['end_time'])->format('H:i') : '--:--' }}">
                </div>
              </div>
            @endforeach
          @else
            <div class="detail-row">
              <div class="detail-label">休憩</div>
              <div class="detail-value">
                <span class="time-display">--:--</span>
                <span class="separator">〜</span>
                <span class="time-display">--:--</span>
              </div>
            </div>
          @endif

          <!-- 備考 -->
          <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
              <textarea class="remarks-textarea" name="remarks">{{ $attendance->remarks }}</textarea>
            </div>
          </div>
      </div>

      <!-- 修正ボタン -->
      @if ($attendance->hasPendingModificationRequest ?? false)
        <div class="pending-message">
          ※承認待ちのため修正はできません
        </div>
      @else
        <div class="modify-button-container">
          <button class="modify-button" type="submit">修正</button>
        </div>
      @endif
      </form>

      <style>
        .attendance-detail-container {
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

        .attendance-detail-card {
          background-color: #FFFFFF;
          border-radius: 10px;
          padding: 40px;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
          margin-bottom: 40px;
        }

        .detail-row {
          display: flex;
          align-items: center;
          padding: 20px 0;
          border-bottom: 2px solid #E1E1E1;
        }

        .detail-row:last-child {
          border-bottom: none;
        }

        .detail-label {
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          line-height: 1.21;
          color: #737373;
          min-width: 120px;
          margin-right: 20px;
        }

        .detail-value {
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          line-height: 1.21;
          color: #000000;
          display: flex;
          align-items: center;
          gap: 8px;
          flex: 1;
        }

        .detail-value .year {
          margin-right: 8px;
        }

        .time-display {
          background-color: #FFFFFF;
          border: 1px solid #E1E1E1;
          border-radius: 4px;
          padding: 4px 8px;
          min-width: 103px;
          height: 29px;
          text-align: center;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          color: #000000;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .detail-value .separator {
          margin: 0 8px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          color: #000000;
        }

        .remarks-display {
          background-color: #FFFFFF;
          border: 1px solid #D9D9D9;
          border-radius: 4px;
          padding: 8px 12px;
          min-height: 72px;
          width: 316px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 14px;
          color: #000000;
          display: flex;
          align-items: center;
        }

        .remarks-textarea {
          background-color: #FFFFFF;
          border: 1px solid #D9D9D9;
          border-radius: 4px;
          padding: 8px 12px;
          min-height: 72px;
          width: 316px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 14px;
          color: #000000;
          resize: vertical;
        }

        .remarks-textarea:focus {
          outline: none;
          border-color: #000000;
        }

        .modify-button-container {
          display: flex;
          justify-content: flex-end;
          margin-bottom: 40px;
        }

        .modify-button {
          background-color: #000000;
          color: #ffffff;
          padding: 11px 41px;
          border: none;
          border-radius: 5px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 22px;
          line-height: 1.21;
          cursor: pointer;
          transition: opacity 0.2s ease;
          width: 130px;
          height: 50px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modify-button:hover {
          opacity: 0.8;
        }

        .modification-form {
          background-color: #FFFFFF;
          border-radius: 10px;
          padding: 40px;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
          margin-bottom: 40px;
        }

        .section-title {
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 24px;
          line-height: 1.21;
          color: #000000;
          margin-bottom: 20px;
          padding-bottom: 10px;
          border-bottom: 2px solid #E1E1E1;
        }

        .form-group {
          margin-bottom: 20px;
        }

        .form-group label {
          display: block;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          line-height: 1.21;
          color: #737373;
          margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
          width: 100%;
          padding: 12px 16px;
          border: 1px solid #E1E1E1;
          border-radius: 4px;
          font-family: 'Inter', sans-serif;
          font-weight: 400;
          font-size: 16px;
          line-height: 1.21;
          color: #000000;
          background-color: #FFFFFF;
          box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
          outline: none;
          border-color: #000000;
          box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
        }

        .form-group textarea {
          height: 100px;
          resize: vertical;
        }

        .time-input-group {
          display: flex;
          gap: 12px;
          align-items: center;
        }

        .time-input {
          width: 103px;
          height: 29px;
          border: 1px solid #E1E1E1;
          border-radius: 4px;
          padding: 4px 8px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          color: #000000;
          background-color: #FFFFFF;
          text-align: center;
        }

        .time-input:focus {
          outline: none;
          border-color: #000000;
        }

        .time-input-group span {
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 16px;
          color: #000000;
        }

        .form-actions {
          display: flex;
          gap: 16px;
          justify-content: flex-end;
          margin-top: 30px;
        }

        .cancel-btn {
          background-color: #FFFFFF;
          color: #000000;
          padding: 12px 32px;
          border: 1px solid #E1E1E1;
          border-radius: 5px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 18px;
          line-height: 1.21;
          cursor: pointer;
          transition: opacity 0.2s ease;
        }

        .cancel-btn:hover {
          opacity: 0.8;
        }

        .submit-btn {
          background-color: #000000;
          color: #FFFFFF;
          padding: 12px 32px;
          border: none;
          border-radius: 5px;
          font-family: 'Inter', sans-serif;
          font-weight: 700;
          font-size: 18px;
          line-height: 1.21;
          cursor: pointer;
          transition: opacity 0.2s ease;
        }

        .submit-btn:hover {
          opacity: 0.8;
        }

        .error-message {
          color: #dc3545;
          font-family: 'Inter', sans-serif;
          font-weight: 400;
          font-size: 14px;
          margin-top: 8px;
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

        .pending-message {
          background-color: #fff3cd;
          color: #856404;
          border: 1px solid #ffeaa7;
          padding: 20px;
          border-radius: 8px;
          margin-bottom: 40px;
          text-align: center;
          font-family: 'Inter', sans-serif;
          font-weight: 800;
          font-size: 18px;
          color: rgba(255, 0, 0, 0.5);
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

          .attendance-detail-card,
          .modification-form {
            padding: 20px;
          }

          .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
          }

          .detail-label {
            min-width: auto;
            margin-right: 0;
          }

          .modify-button-container {
            justify-content: center;
          }
        }

        @media (max-width: 480px) {
          .page-title h1 {
            font-size: 20px;
          }

          .section-title {
            font-size: 20px;
          }

          .detail-label {
            font-size: 14px;
          }

          .detail-value {
            font-size: 14px;
          }

          .form-group label {
            font-size: 14px;
          }

          .form-group input,
          .form-group textarea {
            font-size: 14px;
          }

          .modify-button {
            font-size: 18px;
            padding: 10px 24px;
          }

          .submit-btn,
          .cancel-btn {
            font-size: 16px;
            padding: 10px 24px;
          }
        }
      </style>

      <script>
        function showModifyForm() {
          document.getElementById('modification-form').style.display = 'block';
          document.querySelector('.attendance-detail-card').style.display = 'none';
        }

        function hideModifyForm() {
          document.getElementById('modification-form').style.display = 'none';
          document.querySelector('.attendance-detail-card').style.display = 'block';
        }
      </script>
    @endsection
