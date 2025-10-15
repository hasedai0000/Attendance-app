<x-common.admin-layout title="申請詳細（管理者）" active-page="requests">
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

  <style>
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

    .action-buttons-container {
      display: flex;
      justify-content: flex-end;
      gap: 20px;
      margin-bottom: 40px;
      width: 900px;
    }

    .approval-button {
      width: 130px;
      height: 50px;
      background-color: #000000;
      color: #FFFFFF;
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
      width: 130px;
      height: 50px;
      background-color: #696969;
      color: #FFFFFF;
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

    /* レスポンシブデザイン */
    @media (max-width: 768px) {
      .modification-request-detail-card {
        width: 100%;
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
      .approved-button {
        font-size: 18px;
        padding: 10px 30px;
        width: 100%;
        max-width: 200px;
      }
    }
  </style>
</x-common.admin-layout>
