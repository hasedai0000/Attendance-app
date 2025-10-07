@props(['requests' => [], 'showUser' => false])

<div class="requests-table-container">
  @if (count($requests) > 0)
    <table class="requests-table">
      <thead>
        <tr class="table-header">
          <th>状態</th>
          @if ($showUser)
            <th>名前</th>
          @endif
          <th>対象日時</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($requests as $request)
          <tr class="table-row">
            <td class="status-cell">
              <span class="status-badge status-{{ $request['status'] }}">
                {{ $request['status'] === 'pending' ? '承認待ち' : '承認済み' }}
              </span>
            </td>
            @if ($showUser)
              <td class="name-cell">{{ $request['user']['name'] }}</td>
            @endif
            <td class="date-cell">
              {{ \Carbon\Carbon::parse($request['attendance']['date'])->setTimezone('Asia/Tokyo')->format('Y/m/d') }}
            </td>
            <td class="reason-cell">{{ Str::limit($request['requested_remarks'], 20) ?: '遅延のため' }}</td>
            <td class="date-cell">
              {{ \Carbon\Carbon::parse($request['created_at'])->setTimezone('Asia/Tokyo')->format('Y/m/d') }}
            </td>
            <td class="detail-cell">
              <a href="{{ Auth::user()->is_admin ? route('admin.modification-requests.detail', $request['id']) : route('modification-requests.show', $request['id']) }}"
                class="detail-link">詳細</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="empty-message">
      {{ $requests[0]['status'] ?? 'pending' === 'pending' ? '承認待ちの修正申請はありません。' : '承認済みの修正申請はありません。' }}
    </div>
  @endif
</div>
