@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
<div class="admin-attendance-detail-container">
  <!-- ヘッダー -->
  <x-admin.header active-page="attendance" />

  <!-- メインコンテンツ -->
  <div class="admin-content">
    <!-- タイトル -->
    <div class="page-title">
      <div class="title-line"></div>
      <h1>勤怠詳細</h1>
    </div>

    <!-- 勤怠詳細カード -->
    <div class="attendance-detail-card">
      <!-- 名前セクション -->
      <div class="detail-section">
        <div class="section-content">
          <span class="section-label">名前</span>
          <span class="section-value">{{ $attendance->user->name }}</span>
        </div>
      </div>

      <!-- 日付セクション -->
      <div class="detail-section">
        <div class="section-content">
          <span class="section-label">日付</span>
          <span class="section-value">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</span>
        </div>
      </div>

      <!-- 出勤・退勤セクション -->
      <div class="detail-section">
        <div class="section-content">
          <span class="section-label">出勤・退勤</span>
          <span class="section-value">
            @if($attendance->start_time && $attendance->end_time)
              {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
            @elseif($attendance->start_time)
              {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }} 〜 -
            @else
              - 〜 -
            @endif
          </span>
        </div>
      </div>

      <!-- 休憩セクション -->
      @if($attendance->breaks && count($attendance->breaks) > 0)
        @foreach($attendance->breaks as $index => $break)
          <div class="detail-section">
            <div class="section-content">
              <span class="section-label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
              <span class="section-value">
                @if($break->start_time && $break->end_time)
                  {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}
                @elseif($break->start_time)
                  {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }} 〜 -
                @else
                  - 〜 -
                @endif
              </span>
            </div>
          </div>
        @endforeach
      @endif

      <!-- 備考セクション -->
      @if($attendance->remarks)
        <div class="detail-section">
          <div class="section-content">
            <span class="section-label">備考</span>
            <span class="section-value">{{ $attendance->remarks }}</span>
          </div>
        </div>
      @endif
    </div>

    <!-- 承認ボタン -->
    <div class="approval-button-container">
      <button class="approval-button" onclick="approveAttendance()">承認</button>
    </div>
  </div>
</div>

<style>
.admin-attendance-detail-container {
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

.attendance-detail-card {
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
  min-width: 90px;
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

.approval-button-container {
  display: flex;
  justify-content: flex-end;
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
  
  .approval-button {
    font-size: 18px;
    padding: 10px 30px;
  }
}
</style>

<script>
function approveAttendance() {
  if (confirm('この勤怠を承認しますか？')) {
    // 承認処理を実装
    alert('承認が完了しました。');
  }
}
</script>
@endsection
