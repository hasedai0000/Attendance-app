@props(['staff' => []])

<div class="table-container">
  <table class="table">
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
  .staff-name {
    text-align: center;
  }

  .email-cell {
    text-align: left;
  }
</style>
