@props(['currentMonth', 'routeName', 'routeParams' => []])

@php
  $prevMonth = $currentMonth->copy()->subMonth();
  $nextMonth = $currentMonth->copy()->addMonth();
@endphp

<div class="month-navigation">
  <a href="{{ route($routeName, array_merge($routeParams, ['month' => $prevMonth->format('Y-m')])) }}" class="nav-arrow">
    <img src="{{ asset('images/arrow-left.svg') }}" alt="前月" class="arrow-icon">
    前月
  </a>
  <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
  <a href="{{ route($routeName, array_merge($routeParams, ['month' => $nextMonth->format('Y-m')])) }}" class="nav-arrow">
    翌月
    <img src="{{ asset('images/arrow-right.svg') }}" alt="翌月" class="arrow-icon">
  </a>
</div>
