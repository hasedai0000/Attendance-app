<x-common.attendance-layout title="勤怠詳細" active-page="list">
  @push('styles')
    <link rel="stylesheet" href="{{ asset('css/detail-card.css') }}">
  @endpush

  <!-- 勤怠詳細カード -->
  <div class="detail-card">
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

  @if ($modificationRequest && $modificationRequest->status === 'pending')
    <div class="notice-message">
      *承認待ちのため修正はできません。
    </div>
  @else
    <div class="action-button-container">
      <x-common.action-button type="submit" variant="primary">修正</x-common.action-button>
    </div>
  @endif

  </form>
  </div>
</x-common.attendance-layout>
