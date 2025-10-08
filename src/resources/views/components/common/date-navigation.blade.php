@props(['date', 'routeName', 'routeParams' => []])

@php
  $previousDate = $date->copy()->subDay();
  $nextDate = $date->copy()->addDay();
@endphp

<!-- 日付ナビゲーション -->
<div class="date-navigation">
  <a href="{{ route($routeName, array_merge($routeParams, ['date' => $previousDate->format('Y-m-d')])) }}"
    class="nav-arrow">
    <img src="{{ asset('images/arrow-left.svg') }}" alt="前日" class="arrow-icon">
    前日
  </a>
  <span class="current-month">{{ $date->format('Y年m月d日') }}</span>
  <a href="{{ route($routeName, array_merge($routeParams, ['date' => $nextDate->format('Y-m-d')])) }}" class="nav-arrow">
    翌日
    <img src="{{ asset('images/arrow-right.svg') }}" alt="翌日" class="arrow-icon">
  </a>
</div>

<script>
  function changeDate(direction) {
    const currentDate = new Date('{{ $date->format('Y-m-d') }}');
    currentDate.setDate(currentDate.getDate() + direction);

    const newDate = currentDate.toISOString().split('T')[0];
    window.location.href = `{{ route($routeName, $routeParams) }}?date=${newDate}`;
  }
</script>
