@props(['currentDate', 'routeName', 'routeParams' => []])

@php
  $prevDate = $currentDate->copy()->subDay();
  $nextDate = $currentDate->copy()->addDay();
@endphp

<div class="date-navigation">
  <div class="date-nav-left">
    <a href="{{ route($routeName, array_merge($routeParams, ['date' => $prevDate->format('Y-m-d')])) }}"
      class="date-nav-btn">
      <img src="{{ asset('images/arrow-left.svg') }}" alt="前日" class="arrow-icon">
      前日
    </a>
  </div>

  <div class="current-date">
    <div class="date-display">{{ $currentDate->format('Y/m/d') }}</div>
    <img src="{{ asset('images/calendar-icon.svg') }}" alt="カレンダー" class="calendar-icon">
  </div>

  <div class="date-nav-right">
    <a href="{{ route($routeName, array_merge($routeParams, ['date' => $nextDate->format('Y-m-d')])) }}"
      class="date-nav-btn">
      翌日
      <img src="{{ asset('images/arrow-right.svg') }}" alt="翌日" class="arrow-icon">
    </a>
  </div>
</div>
