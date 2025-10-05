<?php

namespace App\Infrastructure\Repositories\Traits;

trait SearchableTrait
{
 /**
  * 名前による検索を適用
  */
 protected function applyNameSearch($query, string $searchTerm, string $column = 'name')
 {
  return $query->where($column, 'LIKE', '%' . $searchTerm . '%');
 }

 /**
  * 関連テーブルの名前による検索を適用
  */
 protected function applyRelatedNameSearch($query, string $searchTerm, string $relation, string $column = 'name')
 {
  return $query->whereHas($relation, function ($q) use ($searchTerm, $column) {
   $q->where($column, 'like', '%' . $searchTerm . '%');
  });
 }

 /**
  * 日付範囲による検索を適用
  */
 protected function applyDateRangeSearch($query, $startDate, $endDate, string $column = 'date')
 {
  return $query->whereBetween($column, [$startDate, $endDate]);
 }

 /**
  * 特定の日付による検索を適用
  */
 protected function applyDateSearch($query, $date, string $column = 'date')
 {
  return $query->whereDate($column, $date);
 }
}
