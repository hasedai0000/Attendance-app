@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
  <style>
    .attendance-detail-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem;
    }

    .detail-card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-bottom: 2rem;
    }

    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid #eee;
    }

    .detail-row:last-child {
      border-bottom: none;
    }

    .detail-label {
      font-weight: bold;
      color: #333;
      min-width: 120px;
    }

    .detail-value {
      color: #666;
    }

    .breaks-section {
      margin-top: 2rem;
    }

    .breaks-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .breaks-table th,
    .breaks-table td {
      padding: 0.75rem;
      text-align: center;
      border: 1px solid #ddd;
    }

    .breaks-table th {
      background-color: #f8f9fa;
      font-weight: bold;
    }

    .breaks-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .modification-form {
      background-color: #f8f9fa;
      padding: 2rem;
      border-radius: 8px;
      margin-top: 2rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 0.5rem;
      color: #333;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .form-group textarea {
      height: 100px;
      resize: vertical;
    }

    .time-input-group {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .time-input-group input {
      flex: 1;
    }

    .submit-btn {
      background-color: #28a745;
      color: white;
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1rem;
    }

    .submit-btn:hover {
      background-color: #218838;
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

    .error-message {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
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

    .pending-message {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 2rem;
      text-align: center;
    }
  </style>
@endsection

@section('content')
  <div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

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

    <!-- 基本情報 -->
    <div class="detail-card">
      <h2>基本情報</h2>
      <div class="detail-row">
        <span class="detail-label">名前</span>
        <span class="detail-value">{{ $attendance->user->name }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">日付</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年m月d日') }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">出勤時刻</span>
        <span
          class="detail-value">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '未設定' }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">退勤時刻</span>
        <span
          class="detail-value">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '未設定' }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">備考</span>
        <span class="detail-value">{{ $attendance->remarks ?: '未入力' }}</span>
      </div>
    </div>

    <!-- 休憩情報 -->
    <div class="detail-card">
      <h2>休憩情報</h2>
      @if (count($breaks) > 0)
        <table class="breaks-table">
          <thead>
            <tr>
              <th>休憩回数</th>
              <th>開始時刻</th>
              <th>終了時刻</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($breaks as $index => $break)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $break['start_time'] ? \Carbon\Carbon::parse($break['start_time'])->format('H:i') : '未設定' }}</td>
                <td>{{ $break['end_time'] ? \Carbon\Carbon::parse($break['end_time'])->format('H:i') : '未設定' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <p>休憩データがありません。</p>
      @endif
    </div>

    <!-- 修正申請フォーム -->
    @if (Auth::user()->role !== 'admin')
      <!-- 一般ユーザーの場合は修正申請フォーム -->
      @if ($attendance->hasPendingModificationRequest ?? false)
        <div class="pending-message">
          承認待ちのため修正はできません。
        </div>
      @else
        <div class="modification-form">
          <h2>修正申請</h2>
          <form action="{{ route('modification-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            <div class="form-group">
              <label>出勤・退勤時刻</label>
              <div class="time-input-group">
                <input type="time" name="start_time"
                  value="{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}">
                <span>〜</span>
                <input type="time" name="end_time"
                  value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}">
              </div>
              @error('start_time')
                <div class="error-message">{{ $message }}</div>
              @enderror
              @error('end_time')
                <div class="error-message">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label>休憩時間</label>
              @foreach ($breaks as $index => $break)
                <div class="time-input-group">
                  <input type="time" name="breaks[{{ $index }}][start_time]"
                    value="{{ $break['start_time'] ? \Carbon\Carbon::parse($break['start_time'])->format('H:i') : '' }}">
                  <span>〜</span>
                  <input type="time" name="breaks[{{ $index }}][end_time]"
                    value="{{ $break['end_time'] ? \Carbon\Carbon::parse($break['end_time'])->format('H:i') : '' }}">
                </div>
              @endforeach
              <!-- 新しい休憩時間の追加 -->
              <div class="time-input-group">
                <input type="time" name="breaks[{{ count($breaks) }}][start_time]" placeholder="新しい休憩開始">
                <span>〜</span>
                <input type="time" name="breaks[{{ count($breaks) }}][end_time]" placeholder="新しい休憩終了">
              </div>
            </div>

            <div class="form-group">
              <label>備考 *</label>
              <textarea name="remarks" required>{{ old('remarks', $attendance->remarks) }}</textarea>
              @error('remarks')
                <div class="error-message">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="submit-btn">修正申請</button>
          </form>
        </div>
      @endif
    @else
      <!-- 管理者の場合は直接修正フォーム -->
      <div class="modification-form">
        <h2>勤怠情報修正</h2>
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="form-group">
            <label>出勤・退勤時刻</label>
            <div class="time-input-group">
              <input type="time" name="start_time"
                value="{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}">
              <span>〜</span>
              <input type="time" name="end_time"
                value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}">
            </div>
            @error('start_time')
              <div class="error-message">{{ $message }}</div>
            @enderror
            @error('end_time')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group">
            <label>備考 *</label>
            <textarea name="remarks" required>{{ old('remarks', $attendance->remarks) }}</textarea>
            @error('remarks')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="submit-btn">修正</button>
        </form>
      </div>
    @endif

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('attendance.list') }}">勤怠一覧に戻る</a>
      <a href="{{ route('attendance.index') }}">勤怠打刻に戻る</a>
    </div>
  </div>
@endsection
