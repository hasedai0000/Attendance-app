@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
<div class="admin-attendance-detail-container">
  <!-- ヘッダー -->
  <x-admin.header active-page="attendance" />

  <!-- メインコンテンツ -->
  <div class="admin-content">
    <!-- 戻るボタン -->
    <div class="back-button">
      <a href="{{ route('admin.attendance.daily') }}" class="back-link">← 勤怠一覧に戻る</a>
    </div>

    <!-- タイトル -->
    <div class="page-title">
      <div class="title-line"></div>
      <h1>勤怠詳細</h1>
    </div>

    <!-- 勤怠詳細カード -->
    <div class="attendance-detail-card">
      <div class="detail-section">
        <h2 class="section-title">基本情報</h2>
        <div class="detail-grid">
          <div class="detail-item">
            <label>スタッフ名</label>
            <span>{{ $attendance->user->name }}</span>
          </div>
          <div class="detail-item">
            <label>日付</label>
            <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年m月d日') }}</span>
          </div>
          <div class="detail-item">
            <label>ステータス</label>
            <span class="status-badge status-{{ $attendance->status }}">
              {{ $attendance->status === 'not_working' ? '勤務外' : 
                 ($attendance->status === 'working' ? '出勤中' : 
                 ($attendance->status === 'on_break' ? '休憩中' : 
                 ($attendance->status === 'finished' ? '退勤済' : '不明'))) }}
            </span>
          </div>
        </div>
      </div>

      <div class="detail-section">
        <h2 class="section-title">勤務時間</h2>
        <div class="detail-grid">
          <div class="detail-item">
            <label>出勤時刻</label>
            <span>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}</span>
          </div>
          <div class="detail-item">
            <label>退勤時刻</label>
            <span>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}</span>
          </div>
          <div class="detail-item">
            <label>勤務時間</label>
            <span>
              @if($attendance->start_time && $attendance->end_time)
                @php
                  $start = \Carbon\Carbon::parse($attendance->start_time);
                  $end = \Carbon\Carbon::parse($attendance->end_time);
                  $totalMinutes = $start->diffInMinutes($end);
                  
                  // 休憩時間を引く
                  if($attendance->breaks) {
                    foreach($attendance->breaks as $break) {
                      if($break->start_time && $break->end_time) {
                        $breakStart = \Carbon\Carbon::parse($break->start_time);
                        $breakEnd = \Carbon\Carbon::parse($break->end_time);
                        $totalMinutes -= $breakStart->diffInMinutes($breakEnd);
                      }
                    }
                  }
                  
                  $hours = intval($totalMinutes / 60);
                  $minutes = $totalMinutes % 60;
                @endphp
                {{ sprintf('%d:%02d', $hours, $minutes) }}
              @else
                -
              @endif
            </span>
          </div>
        </div>
      </div>

      @if($attendance->breaks && count($attendance->breaks) > 0)
      <div class="detail-section">
        <h2 class="section-title">休憩時間</h2>
        <div class="breaks-list">
          @foreach($attendance->breaks as $index => $break)
            <div class="break-item">
              <div class="break-number">{{ $index + 1 }}回目</div>
              <div class="break-times">
                <span>{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '-' }}</span>
                <span>〜</span>
                <span>{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '未終了' }}</span>
              </div>
              <div class="break-duration">
                @if($break->start_time && $break->end_time)
                  @php
                    $start = \Carbon\Carbon::parse($break->start_time);
                    $end = \Carbon\Carbon::parse($break->end_time);
                    $minutes = $start->diffInMinutes($end);
                    $hours = intval($minutes / 60);
                    $mins = $minutes % 60;
                  @endphp
                  {{ sprintf('%d:%02d', $hours, $mins) }}
                @else
                  -
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      @if($attendance->remarks)
      <div class="detail-section">
        <h2 class="section-title">備考</h2>
        <div class="remarks-content">
          {{ $attendance->remarks }}
        </div>
      </div>
      @endif
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
  padding: 40px 306px;
  max-width: 1512px;
  margin: 0 auto;
  width: 100%;
}

.back-button {
  margin-bottom: 20px;
}

.back-link {
  color: #000000;
  text-decoration: none;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 16px;
  transition: opacity 0.2s ease;
}

.back-link:hover {
  opacity: 0.7;
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
}

.detail-section {
  margin-bottom: 40px;
}

.detail-section:last-child {
  margin-bottom: 0;
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

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-item label {
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 16px;
  line-height: 1.21;
  color: #737373;
}

.detail-item span {
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 18px;
  line-height: 1.21;
  color: #000000;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 700;
}

.status-not_working {
  background-color: #f8f9fa;
  color: #6c757d;
}

.status-working {
  background-color: #d4edda;
  color: #155724;
}

.status-on_break {
  background-color: #fff3cd;
  color: #856404;
}

.status-finished {
  background-color: #d1ecf1;
  color: #0c5460;
}

.breaks-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.break-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 16px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.break-number {
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #000000;
  min-width: 80px;
}

.break-times {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #000000;
  flex: 1;
}

.break-duration {
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #000000;
  min-width: 60px;
  text-align: right;
}

.remarks-content {
  font-family: 'Inter', sans-serif;
  font-weight: 400;
  font-size: 16px;
  line-height: 1.5;
  color: #000000;
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  white-space: pre-wrap;
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
  
  .attendance-detail-card {
    padding: 20px;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .break-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .break-times {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>
@endsection
