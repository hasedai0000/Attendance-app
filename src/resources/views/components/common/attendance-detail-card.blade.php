@props(['attendance', 'showActions' => false, 'actionRoute' => null, 'actionMethod' => 'POST'])

<div class="attendance-detail-card">
  <!-- 名前セクション -->
  <x-common.detail-section label="名前" :value="$attendance->user->name" />

  <!-- 日付セクション -->
  <x-common.detail-section label="日付">
    <span class="year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
    <span class="date">{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}</span>
  </x-common.detail-section>

  <!-- 出勤・退勤セクション -->
  <x-common.detail-section label="出勤・退勤">
    @if ($attendance->start_time && $attendance->end_time)
      {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }} 〜
      {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
    @elseif($attendance->start_time)
      {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }} 〜 -
    @else
      - 〜 -
    @endif
  </x-common.detail-section>

  <!-- 休憩セクション -->
  @if ($attendance->breaks && count($attendance->breaks) > 0)
    @foreach ($attendance->breaks as $index => $break)
      <x-common.detail-section :label="'休憩' . ($index > 0 ? $index + 1 : '')">
        @if ($break->start_time && $break->end_time)
          {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }} 〜
          {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}
        @elseif($break->start_time)
          {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }} 〜 -
        @else
          - 〜 -
        @endif
      </x-common.detail-section>
    @endforeach
  @endif

  <!-- 備考セクション -->
  @if ($attendance->remarks)
    <x-common.detail-section label="備考" :value="$attendance->remarks" />
  @endif

  @if ($showActions && $actionRoute)
    <form action="{{ $actionRoute }}" method="{{ $actionMethod }}">
      @csrf
      @if ($actionMethod !== 'POST')
        @method($actionMethod)
      @endif
      <div class="action-button-container">
        <x-common.action-button type="submit" variant="primary">承認</x-common.action-button>
      </div>
    </form>
  @endif
</div>
