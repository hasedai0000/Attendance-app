@props(['activePage' => 'attendance'])

<div class="attendance-header">
  <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
  <nav class="attendance-nav">
    <a href="{{ route('admin.attendance.daily') }}"
      class="nav-item {{ $activePage === 'attendance' ? 'active' : '' }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}" class="nav-item {{ $activePage === 'staff' ? 'active' : '' }}">スタッフ一覧</a>
    <a href="{{ route('modification-requests.index') }}"
      class="nav-item {{ $activePage === 'requests' ? 'active' : '' }}">申請一覧</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-item">ログアウト</button>
    </form>
  </nav>
</div>
