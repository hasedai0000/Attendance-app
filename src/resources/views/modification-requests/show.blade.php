<x-common.attendance-layout title="勤怠詳細" active-page="requests">
  @push('styles')
    <link rel="stylesheet" href="{{ asset('css/detail-card.css') }}">
  @endpush

  <!-- 申請詳細カード -->
  <div class="detail-card">
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
        <span class="date">{{ \Carbon\Carbon::parse($modificationRequest->attendance->date)->format('m月d日') }}</span>
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
    @if (count($modificationRequest->modificationRequestBreaks) > 0)
      @foreach ($modificationRequest->modificationRequestBreaks as $index => $break)
        <div class="detail-row">
          <div class="detail-label">休憩{{ $index + 1 }}</div>
          <div class="detail-value">
            <span
              class="time-display requested">{{ $break->requested_start_time ? \Carbon\Carbon::parse($break->requested_start_time)->format('H:i') : '--:--' }}</span>
            <span class="separator">〜</span>
            <span
              class="time-display requested">{{ $break->requested_end_time ? \Carbon\Carbon::parse($break->requested_end_time)->format('H:i') : '--:--' }}</span>
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
      <div class="detail-value">{{ $modificationRequest->requested_remarks }}</div>
    </div>
  </div>

  <!-- 注意メッセージ -->
  @if ($modificationRequest->status === 'pending')
    <div class="notice-message">
      *承認待ちのため修正はできません。
    </div>
  @endif
</x-common.attendance-layout>
