@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
  <style>
    .staff-list-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .staff-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .staff-table th,
    .staff-table td {
      padding: 0.75rem;
      text-align: center;
      border: 1px solid #ddd;
    }

    .staff-table th {
      background-color: #f8f9fa;
      font-weight: bold;
    }

    .staff-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .staff-table tr:hover {
      background-color: #e8f4f8;
    }

    .detail-btn {
      padding: 0.25rem 0.75rem;
      background-color: #17a2b8;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.875rem;
    }

    .detail-btn:hover {
      background-color: #138496;
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

    .empty-message {
      text-align: center;
      padding: 2rem;
      color: #666;
      font-style: italic;
    }
  </style>
@endsection

@section('content')
  <div class="staff-list-container">
    <h1>スタッフ一覧</h1>

    @if (count($staff) > 0)
      <table class="staff-table">
        <thead>
          <tr>
            <th>氏名</th>
            <th>メールアドレス</th>
            <th>登録日</th>
            <th>勤怠詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($staff as $user)
            <tr>
              <td>{{ $user['name'] }}</td>
              <td>{{ $user['email'] }}</td>
              <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('Y/m/d') }}</td>
              <td>
                <a href="{{ route('admin.staff.attendance', $user['id']) }}" class="detail-btn">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty-message">
        スタッフデータがありません。
      </div>
    @endif

    <!-- ナビゲーションリンク -->
    <div class="nav-links">
      <a href="{{ route('admin.index') }}">管理者ダッシュボードに戻る</a>
      <a href="{{ route('admin.attendance.daily') }}">日次勤怠一覧</a>
    </div>
  </div>
@endsection
