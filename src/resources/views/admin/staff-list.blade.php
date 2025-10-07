<x-common.admin-layout title="スタッフ一覧" active-page="staff">
  <!-- スタッフ一覧テーブル -->
  <div class="staff-table-container">
    <table class="staff-table">
      <thead>
        <tr class="table-header">
          <th>名前</th>
          <th>メールアドレス</th>
          <th>月次勤怠</th>
        </tr>
      </thead>
      <tbody>
        @forelse($staff as $user)
          <tr class="table-row">
            <td class="staff-name">{{ $user['name'] }}</td>
            <td class="email-cell">{{ $user['email'] }}</td>
            <td class="detail-cell">
              <a href="{{ route('admin.staff.attendance', $user['id']) }}" class="detail-link">詳細</a>
            </td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="3" class="no-data">スタッフデータがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <style>
    .staff-table-container {
      background-color: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .staff-table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-header {
      background-color: #FFFFFF;
      border-bottom: 3px solid #E1E1E1;
    }

    .table-header th {
      padding: 15px 36px;
      text-align: center;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
    }

    .table-row {
      border-bottom: 2px solid #E1E1E1;
    }

    .table-row:last-child {
      border-bottom: none;
    }

    .table-row td {
      padding: 15px 36px;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.21;
      letter-spacing: 0.15em;
      color: #737373;
    }

    .staff-name {
      text-align: center;
    }

    .email-cell {
      text-align: left;
    }

    .detail-link {
      color: #000000;
      text-decoration: none;
      transition: opacity 0.2s ease;
    }

    .detail-link:hover {
      opacity: 0.7;
    }

    .no-data {
      text-align: center;
      color: #737373;
      font-style: italic;
    }

    /* レスポンシブデザイン */
    @media (max-width: 768px) {
      .staff-table-container {
        overflow-x: auto;
      }

      .table-header th,
      .table-row td {
        padding: 12px 16px;
        font-size: 14px;
      }
    }
  </style>
</x-common.admin-layout>
