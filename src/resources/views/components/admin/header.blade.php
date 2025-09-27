@props(['activePage' => 'attendance'])

<div class="admin-header">
  <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
  <nav class="admin-nav">
    <a href="{{ route('admin.attendance.daily') }}"
      class="nav-item {{ $activePage === 'attendance' ? 'active' : '' }}">勤怠一覧</a>
    <a href="{{ route('admin.staff.list') }}" class="nav-item {{ $activePage === 'staff' ? 'active' : '' }}">スタッフ一覧</a>
    <a href="{{ route('admin.modification-requests.index') }}"
      class="nav-item {{ $activePage === 'requests' ? 'active' : '' }}">申請一覧</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-item">ログアウト</button>
    </form>
  </nav>
</div>

<style>
  .admin-header {
    background-color: #000000;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
  }

  .logo {
    height: 36px;
    width: auto;
  }

  .admin-nav {
    display: flex;
    gap: 40px;
    align-items: center;
  }

  .nav-item {
    color: #FFFFFF;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 24px;
    line-height: 1.21;
    transition: opacity 0.2s ease;
  }

  .nav-item:hover {
    opacity: 0.8;
  }

  .nav-item.active {
    color: #FFFFFF;
  }

  /* レスポンシブデザイン */
  @media (max-width: 1200px) {
    .admin-nav {
      gap: 20px;
    }

    .nav-item {
      font-size: 18px;
    }
  }

  @media (max-width: 768px) {
    .admin-header {
      flex-direction: column;
      height: auto;
      padding: 20px;
      gap: 20px;
    }

    .admin-nav {
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
    }

    .nav-item {
      font-size: 16px;
    }
  }
</style>
