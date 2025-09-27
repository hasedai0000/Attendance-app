@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<div class="login-container">
  <!-- ヘッダー -->
  <div class="login-header">
    <img src="{{ asset('images/coachtech-logo.svg') }}" alt="CoachTech" class="logo">
  </div>

  <!-- ログインフォーム -->
  <div class="login-form-container">
    <div class="login-title">
      <h1>ログイン</h1>
    </div>

    <form class="login-form" action="/login" method="post">
      @csrf
      
      <!-- メールアドレス -->
      <div class="form-group">
        <label for="email" class="form-label">メールアドレス</label>
        <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
        @error('email')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <!-- パスワード -->
      <div class="form-group">
        <label for="password" class="form-label">パスワード</label>
        <input type="password" id="password" name="password" class="form-input" required>
        @error('password')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <!-- ログインボタン -->
      <div class="form-group">
        <button type="submit" class="login-button">ログインする</button>
      </div>
    </form>

    <!-- 会員登録リンク -->
    <div class="register-link">
      <a href="/register" class="register-link-text">会員登録はこちら</a>
    </div>
  </div>
</div>

<style>
.login-container {
  min-height: 100vh;
  background-color: #FFFFFF;
  display: flex;
  flex-direction: column;
}

.login-header {
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

.login-form-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  max-width: 1096px;
  margin: 0 auto;
  width: 100%;
}

.login-title {
  margin-bottom: 80px;
}

.login-title h1 {
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 36px;
  line-height: 1.21;
  color: #000000;
  margin: 0;
  text-align: center;
}

.login-form {
  width: 100%;
  max-width: 680px;
}

.form-group {
  margin-bottom: 30px;
}

.form-group:last-of-type {
  margin-bottom: 40px;
}

.form-label {
  display: block;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 24px;
  line-height: 1.21;
  color: #000000;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  height: 60px;
  border: 1px solid #000000;
  border-radius: 4px;
  padding: 0 20px;
  font-family: 'Inter', sans-serif;
  font-weight: 400;
  font-size: 16px;
  line-height: 1.21;
  color: #000000;
  background-color: #FFFFFF;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #000000;
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

.form-input::placeholder {
  color: #999999;
}

.form-error {
  margin-top: 8px;
  font-family: 'Inter', sans-serif;
  font-weight: 400;
  font-size: 14px;
  color: #FF0000;
}

.login-button {
  width: 100%;
  height: 60px;
  background-color: #000000;
  color: #FFFFFF;
  border: none;
  border-radius: 5px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  font-size: 26px;
  line-height: 1.21;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.login-button:hover {
  opacity: 0.8;
}

.login-button:active {
  opacity: 0.6;
}

.register-link {
  text-align: center;
  margin-top: 20px;
}

.register-link-text {
  font-family: 'Inter', sans-serif;
  font-weight: 400;
  font-size: 20px;
  line-height: 1.21;
  color: #0073CC;
  text-decoration: none;
  transition: opacity 0.2s ease;
}

.register-link-text:hover {
  opacity: 0.8;
  text-decoration: underline;
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
  .login-form-container {
    padding: 20px 16px;
  }
  
  .login-title {
    margin-bottom: 40px;
  }
  
  .login-title h1 {
    font-size: 28px;
  }
  
  .form-label {
    font-size: 20px;
  }
  
  .form-input {
    height: 50px;
    font-size: 14px;
  }
  
  .login-button {
    height: 50px;
    font-size: 22px;
  }
  
  .register-link-text {
    font-size: 18px;
  }
}

@media (max-width: 480px) {
  .login-title h1 {
    font-size: 24px;
  }
  
  .form-label {
    font-size: 18px;
  }
  
  .form-input {
    height: 45px;
    font-size: 14px;
  }
  
  .login-button {
    height: 45px;
    font-size: 20px;
  }
  
  .register-link-text {
    font-size: 16px;
  }
}
</style>
@endsection
