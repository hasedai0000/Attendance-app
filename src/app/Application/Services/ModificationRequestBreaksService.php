<?php

namespace App\Application\Services;

use App\Models\ModificationRequestBreaks;

class ModificationRequestBreaksService
{
  public function __construct() {}

  /**
   * 休憩時間の修正申請を作成
   */
  public function createRequest(string $modificationRequestId, array $breakData): ModificationRequestBreaks
  {
    return ModificationRequestBreaks::create([
      'modification_request_id' => $modificationRequestId,
      'requested_start_time' => $breakData['start_time'] ?? null,
      'requested_end_time' => $breakData['end_time'] ?? null,
    ]);
  }
}
