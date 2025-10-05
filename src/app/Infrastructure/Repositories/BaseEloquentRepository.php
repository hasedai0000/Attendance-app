<?php

namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseEloquentRepository
{
  protected Model $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  /**
   * レコードを作成
   */
  public function create(array $data)
  {
    return $this->model::create($data);
  }

  /**
   * レコードを更新
   */
  public function update(string $id, array $data)
  {
    $record = $this->model::findOrFail($id);
    $record->update($data);
    return $record->fresh();
  }

  /**
   * IDでレコードを取得
   */
  public function findById(string $id)
  {
    return $this->model::find($id);
  }

  /**
   * 全レコードを取得（検索機能付き）
   */
  public function findAll(string $searchTerm = ''): array
  {
    $query = $this->model::query();

    if ($searchTerm) {
      $query = $this->applySearchFilter($query, $searchTerm);
    }

    return $query->get()->toArray();
  }

  /**
   * 検索フィルターを適用（サブクラスで実装）
   */
  protected function applySearchFilter($query, string $searchTerm)
  {
    // デフォルト実装：nameカラムで検索
    if (method_exists($this->model, 'getTable')) {
      $tableName = $this->model->getTable();
      $query->where($tableName . '.name', 'like', '%' . $searchTerm . '%');
    }

    return $query;
  }

  /**
   * レコードを削除
   */
  public function delete(string $id): bool
  {
    $record = $this->model::findOrFail($id);
    return $record->delete();
  }

  /**
   * レコードの存在確認
   */
  public function exists(string $id): bool
  {
    return $this->model::where('id', $id)->exists();
  }
}
