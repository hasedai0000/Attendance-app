<?php

namespace Tests\Helpers;

use Mockery;
use Illuminate\Foundation\Application;

/**
 * テスト用のモックヘルパークラス
 * データベースに依存しないテストを実現するためのユーティリティ
 */
class MockHelper
{
  /**
   * @var Application
   */
  private Application $app;

  public function __construct(Application $app)
  {
    $this->app = $app;
  }

  /**
   * すべてのモックをクリアする
   */
  public function clearAllMocks(): void
  {
    Mockery::close();
  }
}
