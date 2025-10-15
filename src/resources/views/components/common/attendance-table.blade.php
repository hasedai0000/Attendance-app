@props(['attendances' => [], 'showDetail' => true, 'showUserNames' => false])

<div class="attendance-table-container">
  @if (count($attendances) > 0)
    <table class="attendance-table">
      <thead>
        <tr class="table-header">
          <th>
            @if ($showUserNames)
              名前
            @else
              日付
            @endif
          </th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          @if ($showDetail)
            <th>詳細</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($attendances as $attendance)
          <tr class="table-row">
            <td class="date-cell">
              @if ($showUserNames)
                {{ $attendance['user']['name'] ?? '不明' }}
              @else
                @php
                  $date = \Carbon\Carbon::parse($attendance['date'])->setTimezone('Asia/Tokyo');
                  $dayOfWeekNames = ['日', '月', '火', '水', '木', '金', '土'];
                  $dayOfWeekShort = $dayOfWeekNames[$date->dayOfWeek];
                @endphp
                {{ $date->format('m/d') }}({{ $dayOfWeekShort }})
              @endif
            </td>
            <td class="time-cell">
              {{ $attendance['start_time'] ? \Carbon\Carbon::parse($attendance['start_time'])->setTimezone('Asia/Tokyo')->format('H:i') : '-' }}
            </td>
            <td class="time-cell">
              {{ $attendance['end_time'] ? \Carbon\Carbon::parse($attendance['end_time'])->setTimezone('Asia/Tokyo')->format('H:i') : '-' }}
            </td>
            <td class="time-cell">
              @php
                $totalBreakMinutes = 0;
                if (isset($attendance['breaks']) && is_array($attendance['breaks'])) {
                    foreach ($attendance['breaks'] as $break) {
                        if (isset($break['start_time']) && isset($break['end_time'])) {
                            $start = \Carbon\Carbon::parse($break['start_time'])->setTimezone('Asia/Tokyo');
                            $end = \Carbon\Carbon::parse($break['end_time'])->setTimezone('Asia/Tokyo');
                            $totalBreakMinutes += $start->diffInMinutes($end);
                        }
                    }
                }
                $breakHours = intval($totalBreakMinutes / 60);
                $breakMins = $totalBreakMinutes % 60;
              @endphp
              {{ $totalBreakMinutes > 0 ? sprintf('%d:%02d', $breakHours, $breakMins) : '-' }}
            </td>
            <td class="time-cell">
              @if ($attendance['start_time'] && $attendance['end_time'])
                @php
                  $start = \Carbon\Carbon::parse($attendance['start_time'])->setTimezone('Asia/Tokyo');
                  $end = \Carbon\Carbon::parse($attendance['end_time'])->setTimezone('Asia/Tokyo');
                  $totalMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
                  $workHours = intval($totalMinutes / 60);
                  $workMins = $totalMinutes % 60;
                @endphp
                {{ sprintf('%d:%02d', $workHours, $workMins) }}
              @else
                -
              @endif
            </td>
            @if ($showDetail)
              <td class="detail-cell">
                <a href="{{ route('attendance.detail', $attendance['id']) }}" class="detail-link">詳細</a>
              </td>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="no-data">
      この月の勤怠記録はありません。
    </div>
  @endif
</div>
