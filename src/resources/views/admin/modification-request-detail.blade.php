@extends('layouts.app')

@section('title', '申請詳細（管理者）')

@section('content')
  <div class="admin-modification-request-detail-container">
    <!-- ヘッダー -->
    <x-admin.header active-page="requests" />

    <!-- メインコンテンツ -->
    <div class="admin-content">
      <!-- タイトル -->
      <div class="page-title">
        <div class="title-line"></div>
        <h1>勤怠詳細</h1>
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

      <!-- 申請詳細カード -->
      <div class="modification-request-detail-card">
        <!-- 申請者セクション -->
        <div class="detail-section">
          <div class="section-content">
            <span class="section-label">名前</span>
            <span class="section-value">{{ $modificationRequest->user->name }}</span>
          </div>
        </div>

        <!-- 対象日時セクション -->
        <div class="detail-section">
          <div class="section-content">
            <span class="section-label">日時</span>
            <span
              class="section-value">{{ \Carbon\Carbon::parse($modificationRequest->attendance->date)->format('Y年m月d日') }}</span>
          </div>
        </div>

        <!-- 申請内容セクション -->
        <div class="detail-section">
          <div class="section-content">
            <span class="section-label">出勤・退勤</span>
            <span class="section-value">
              @if ($modificationRequest->requested_start_time && $modificationRequest->requested_end_time)
                {{ \Carbon\Carbon::parse($modificationRequest->requested_start_time)->format('H:i') }} 〜
                {{ \Carbon\Carbon::parse($modificationRequest->requested_end_time)->format('H:i') }}
              @elseif($modificationRequest->requested_start_time)
                {{ \Carbon\Carbon::parse($modificationRequest->requested_start_time)->format('H:i') }} 〜 -
              @elseif($modificationRequest->requested_end_time)
                - 〜 {{ \Carbon\Carbon::parse($modificationRequest->requested_end_time)->format('H:i') }}
              @else
                - 〜 -
              @endif
            </span>
          </div>
        </div>

        <!-- 申請内容（休憩時間） -->
        @if ($modificationRequest->modificationRequestBreaks && count($modificationRequest->modificationRequestBreaks) > 0)
          @foreach ($modificationRequest->modificationRequestBreaks as $index => $break)
            <div class="detail-section">
              <div class="section-content">
                <span class="section-label">休憩{{ $index + 1 }}</span>
                <span class="section-value">
                  @if ($break->requested_start_time && $break->requested_end_time)
                    {{ \Carbon\Carbon::parse($break->requested_start_time)->format('H:i') }} 〜
                    {{ \Carbon\Carbon::parse($break->requested_end_time)->format('H:i') }}
                  @elseif($break->requested_start_time)
                    {{ \Carbon\Carbon::parse($break->requested_start_time)->format('H:i') }} 〜 -
                  @elseif($break->requested_end_time)
                    - 〜 {{ \Carbon\Carbon::parse($break->requested_end_time)->format('H:i') }}
                  @else
                    - 〜 -
                  @endif
                </span>
              </div>
            </div>
          @endforeach
        @else
          <div class="detail-section">
            <div class="section-content">
              <span class="section-label">休憩</span>
              <span class="section-value">
                <span class="time-display">--:--</span>
                <span class="separator">〜</span>
                <span class="time-display">--:--</span>
              </span>
            </div>
          </div>
        @endif

        <!-- 申請理由セクション -->
        <div class="detail-section">
          <div class="section-content">
            <span class="section-label">備考</span>
            <span class="section-value">{{ $modificationRequest->requested_remarks }}</span>
          </div>
        </div>

        <!-- 承認情報セクション（承認済みの場合） -->
        @if ($modificationRequest->status === 'approved' && $modificationRequest->approved_at)
          <div class="detail-section">
            <div class="section-content">
              <span class="section-label">承認日時</span>
              <span
                class="section-value">{{ \Carbon\Carbon::parse($modificationRequest->approved_at)->format('Y年m月d日 H:i') }}</span>
            </div>
          </div>
        @endif

      </div>

      <!-- 承認・却下ボタン（承認待ちの場合のみ） -->
      @if ($modificationRequest->status === 'pending')
        <div class="action-buttons-container">
          <form action="{{ route('admin.modification-requests.approve', $modificationRequest->id) }}" method="POST"
            style="display: inline;">
            @csrf
            <input type="hidden" name="attendance-request-id" value="{{ $modificationRequest->id }}">
            <button class="approval-button" type="submit">承認</button>
          </form>
        </div>
      @else
        <div class="action-buttons-container">
          <button class="approved-button" type="submit">承認済み</button>
        </div>
      @endif

    </div>
  </div>

  <style>
    .admin-modification-request-detail-container {
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

    .modification-request-detail-card {
      width: 900px;
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 40px;
    }

    .detail-section {
      padding: 30px 64px;
      border-bottom: 2px solid #E1E1E1;
    }

    .detail-section:last-child {
      border-bottom: none;
    }

    .section-content {
      display: flex;
      align-items: center;
      gap: 64px;
    }

    .section-label {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
      min-width: 120px;
    }

    .section-value {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #000000;
      flex: 1;
    }

    .status-badge {
      padding: 4px 12px;
      border-radius: 4px;
      font-size: 14px;
      font-weight: 700;
    }

    .status-pending {
      background-color: #FFF3CD;
      color: #856404;
    }

    .status-approved {
      background-color: #D4EDDA;
      color: #155724;
    }

    .status-rejected {
      background-color: #F8D7DA;
      color: #721C24;
    }

    .action-buttons-container {
      display: flex;
      justify-content: flex-end;
      gap: 20px;
      margin-bottom: 40px;
    }

    .approval-button {
      background-color: #000000;
      color: #FFFFFF;
      padding: 11px 41px;
      border-radius: 5px;
      border: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: opacity 0.2s ease;
    }

    .approval-button:hover {
      opacity: 0.8;
    }

    .approved-button {
      background-color: #696969;
      color: #FFFFFF;
      padding: 11px 41px;
      border-radius: 5px;
      border: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: opacity 0.2s ease;
    }


    .reject-button {
      background-color: #DC3545;
      color: #FFFFFF;
      padding: 11px 41px;
      border-radius: 5px;
      border: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: opacity 0.2s ease;
    }

    .reject-button:hover {
      opacity: 0.8;
    }

    .back-button-container {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 40px;
    }

    .back-button {
      background-color: #6C757D;
      color: #FFFFFF;
      padding: 11px 41px;
      border-radius: 5px;
      text-decoration: none;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 22px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      cursor: pointer;
      transition: opacity 0.2s ease;
      display: inline-block;
    }

    .back-button:hover {
      opacity: 0.8;
    }

    .message {
      padding: 15px 20px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 16px;
    }

    .message-success {
      background-color: #D4EDDA;
      color: #155724;
      border: 1px solid #C3E6CB;
    }

    .message-error {
      background-color: #F8D7DA;
      color: #721C24;
      border: 1px solid #F5C6CB;
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

      .detail-section {
        padding: 20px 32px;
      }

      .section-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .section-label {
        min-width: auto;
      }

      .action-buttons-container {
        flex-direction: column;
        align-items: center;
      }

      .approval-button,
      .reject-button,
      .back-button {
        font-size: 18px;
        padding: 10px 30px;
        width: 100%;
        max-width: 200px;
      }
    }
  </style>

@endsection
