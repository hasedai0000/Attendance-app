<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
  /**
   * ユーザーがログインしているかどうかを判定
   *
   * @return bool
   */
  public function isAuthenticated(): bool
  {
    return Auth::check();
  }

  /**
   * 現在ログインしているユーザーのIDを取得
   *
   * @return string|null
   */
  public function getCurrentUserId(): ?string
  {
    return Auth::id();
  }

  /**
   * 現在ログインしているユーザーを取得
   *
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function getCurrentUser()
  {
    return Auth::user();
  }

  /**
   * 認証が必要な処理で、未認証の場合は例外を投げる
   *
   * @return string
   * @throws \Exception
   */
  public function requireAuthentication(): string
  {
    if (!$this->isAuthenticated()) {
      throw new \Exception('認証が必要です。');
    }

    return $this->getCurrentUserId();
  }

  /**
   * 現在ログインしているユーザーが管理者かどうかを判定
   *
   * @return bool
   */
  public function isAdmin(): bool
  {
    if (!$this->isAuthenticated()) {
      return false;
    }

    $user = $this->getCurrentUser();
    return $user && $user->is_admin;
  }

  /**
   * 管理者権限が必要な処理で、管理者でない場合は例外を投げる
   *
   * @return string
   * @throws \Exception
   */
  public function requireAdmin(): string
  {
    $userId = $this->requireAuthentication();

    if (!$this->isAdmin()) {
      throw new \Exception('管理者権限が必要です。');
    }

    return $userId;
  }
}
