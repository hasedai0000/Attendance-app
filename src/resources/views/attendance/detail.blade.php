<x-common.attendance-layout title="勤怠詳細" active-page="list">
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

<style>
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

  /* レスポンシブデザイン */
  @media (max-width: 768px) {
    .attendance-detail-card {
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
  }
</style>
