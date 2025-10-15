@extends('layouts.app')

@section('content')
  <div class="admin-login-container">
    <!-- ヘッダー -->
    <div class="admin-header">
      <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
    </div>

    <!-- メインコンテンツ -->
    <div class="admin-login-content">
      <h1 class="admin-title">管理者ログイン</h1>

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.login') }}" class="admin-form">
        @csrf

        <div class="form-group">
          <label for="email" class="form-label">メールアドレス</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
            class="form-input">
        </div>

        <div class="form-group">
          <label for="password" class="form-label">パスワード</label>
          <input type="password" id="password" name="password" required class="form-input">
        </div>

        <button type="submit" class="admin-login-btn">管理者ログインする</button>
      </form>
    </div>
  </div>

  <style>
    .admin-login-container {
      min-height: 100vh;
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
    }

    .admin-header {
      background-color: #000000;
      height: 80px;
      display: flex;
      align-items: center;
      padding: 0 25px;
    }

    .logo {
      height: 36px;
      width: auto;
    }

    .admin-login-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .admin-title {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 36px;
      line-height: 1.21;
      color: #000000;
      text-align: center;
      margin-bottom: 72px;
    }

    .admin-form {
      width: 100%;
      max-width: 680px;
      display: flex;
      flex-direction: column;
      gap: 36px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-label {
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 24px;
      line-height: 1.21;
      color: #000000;
    }

    .form-input {
      width: 100%;
      height: 60px;
      border: 1px solid #000000;
      border-radius: 4px;
      padding: 0 16px;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      box-sizing: border-box;
    }

    .form-input:focus {
      outline: none;
      border-color: #000000;
      box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
    }

    .admin-login-btn {
      width: 100%;
      height: 60px;
      background-color: #000000;
      color: #ffffff;
      border: none;
      border-radius: 5px;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 26px;
      line-height: 1.21;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .admin-login-btn:hover {
      background-color: #333333;
    }

    .admin-login-btn:active {
      background-color: #666666;
    }

    .auth-links {
      margin-top: 40px;
      text-align: center;
    }

    .auth-links a {
      color: #000000;
      text-decoration: underline;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
    }

    .auth-links a:hover {
      color: #333333;
    }

    .alert {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px 16px;
      border: 1px solid #f5c6cb;
      border-radius: 4px;
      margin-bottom: 20px;
      width: 100%;
      max-width: 680px;
    }

    .alert ul {
      margin: 0;
      padding-left: 20px;
    }

    .alert li {
      margin-bottom: 4px;
    }

    /* レスポンシブデザイン */
    @media (max-width: 768px) {
      .admin-login-content {
        padding: 20px 16px;
      }

      .admin-title {
        font-size: 28px;
        margin-bottom: 40px;
      }

      .admin-form {
        gap: 24px;
      }

      .form-label {
        font-size: 20px;
      }

      .form-input {
        height: 50px;
        font-size: 14px;
      }

      .admin-login-btn {
        height: 50px;
        font-size: 20px;
      }
    }
  </style>
@endsection
