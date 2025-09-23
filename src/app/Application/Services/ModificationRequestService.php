<?php

namespace App\Application\Services;

use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Domain\ModificationRequest\Entities\ModificationRequest;
use App\Domain\ModificationRequest\Repositories\ModificationRequestInterface;
use App\Domain\ModificationRequest\ValueObjects\Status;
use App\Domain\ModificationRequestBreaks\Repositories\ModificationRequestBreaksInterface;
use App\Models\ModificationRequest as ModificationRequestModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModificationRequestService
{
  public function __construct(
    private ModificationRequestInterface $modificationRequestRepository,
    private AttendanceRepositoryInterface $attendanceRepository,
    private ModificationRequestBreaksInterface $modificationRequestBreaksRepository
  ) {}

  /**
   * 修正申請を作成
   */
  public function createRequest(string $attendanceId, string $userId, array $data): ModificationRequestModel
  {
    // 勤怠記録の存在確認
    $attendance = $this->attendanceRepository->findById($attendanceId);
    if (!$attendance) {
      throw new \DomainException('勤怠記録が見つかりません');
    }

    // 既に修正申請が存在するかチェック
    $existingRequest = ModificationRequestModel::where('attendance_id', $attendanceId)
      ->where('status', 'pending')
      ->first();
    if ($existingRequest) {
      throw new \DomainException('既に修正申請が存在します');
    }

    return ModificationRequestModel::create([
      'attendance_id' => $attendanceId,
      'user_id' => $userId,
      'requested_start_time' => $data['start_time'],
      'requested_end_time' => $data['end_time'],
      'requested_remarks' => $data['remarks'],
      'status' => 'pending',
    ]);
  }

  /**
   * ユーザーの承認待ち修正申請を取得
   */
  public function getPendingRequestsByUser(string $userId): array
  {
    return ModificationRequestModel::where('user_id', $userId)
      ->where('status', 'pending')
      ->with(['attendance'])
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * ユーザーの承認済み修正申請を取得
   */
  public function getApprovedRequestsByUser(string $userId): array
  {
    return ModificationRequestModel::where('user_id', $userId)
      ->where('status', 'approved')
      ->with(['attendance'])
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * 承認待ちの修正申請一覧を取得（管理者用）
   */
  public function getPendingModificationRequests(): array
  {
    return ModificationRequestModel::where('status', 'pending')
      ->with(['attendance.user', 'user'])
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * 承認済みの修正申請一覧を取得（管理者用）
   */
  public function getApprovedModificationRequests(): array
  {
    return ModificationRequestModel::where('status', 'approved')
      ->with(['attendance.user', 'user'])
      ->orderBy('created_at', 'desc')
      ->get()
      ->toArray();
  }

  /**
   * 修正申請を承認
   */
  public function approveModificationRequest(string $modificationRequestId): ModificationRequestModel
  {
    $modificationRequest = ModificationRequestModel::findOrFail($modificationRequestId);

    if ($modificationRequest->status !== 'pending') {
      throw new \DomainException('承認待ちの修正申請のみ承認できます');
    }

    // 勤怠記録を更新
    $updateData = [];
    if ($modificationRequest->requested_start_time) {
      $updateData['start_time'] = $modificationRequest->requested_start_time;
    }
    if ($modificationRequest->requested_end_time) {
      $updateData['end_time'] = $modificationRequest->requested_end_time;
    }
    if ($modificationRequest->requested_remarks) {
      $updateData['remarks'] = $modificationRequest->requested_remarks;
    }

    if (!empty($updateData)) {
      $this->attendanceRepository->update($modificationRequest->attendance_id, $updateData);
    }

    // 修正申請を承認済みに更新
    $modificationRequest->update([
      'status' => 'approved',
      'approved_by' => Auth::id(),
      'approved_at' => now(),
    ]);

    return $modificationRequest;
  }

  /**
   * 修正申請詳細を取得
   */
  public function getModificationRequestDetail(string $id): ?ModificationRequestModel
  {
    return ModificationRequestModel::with(['attendance.user', 'user'])
      ->find($id);
  }

  // 既存のメソッドも残しておく（互換性のため）
  public function createModificationRequest(array $data): ModificationRequest
  {
    $userId = Auth::id();
    $attendanceId = $data['attendance_id'];
    $requestedStartTime = $data['requested_start_time'] ?? '';
    $requestedEndTime = $data['requested_end_time'] ?? '';
    $requestedRemarks = $data['requested_remarks'];

    // 勤怠記録の存在確認
    $attendance = $this->attendanceRepository->findById($attendanceId);
    if (!$attendance) {
      throw new \DomainException('勤怠記録が見つかりません');
    }

    // 既に修正申請が存在するかチェック
    $existingRequest = $this->modificationRequestRepository->findByAttendanceIdAndStatus($attendanceId, Status::PENDING);
    if ($existingRequest) {
      throw new \DomainException('既に修正申請が存在します');
    }

    $modificationRequest = new ModificationRequest(
      Str::uuid(),
      $attendanceId,
      $userId,
      $requestedStartTime,
      $requestedEndTime,
      $requestedRemarks,
      new Status(Status::PENDING),
      '',
      ''
    );

    $this->modificationRequestRepository->save($modificationRequest);

    // 休憩時間の修正申請も処理
    if (isset($data['breaks']) && is_array($data['breaks'])) {
      foreach ($data['breaks'] as $breakData) {
        $this->modificationRequestBreaksRepository->create(
          $modificationRequest->getId(),
          $breakData['requested_start_time'],
          $breakData['requested_end_time'] ?? ''
        );
      }
    }

    return $modificationRequest;
  }

  /**
   * ユーザーの修正申請一覧を取得
   */
  public function getUserModificationRequests(): array
  {
    $userId = Auth::id();
    return $this->modificationRequestRepository->findByUserId($userId);
  }

  /**
   * 修正申請を承認（既存メソッド）
   */
  public function approveModificationRequestOld(string $modificationRequestId): ModificationRequest
  {
    $modificationRequest = $this->modificationRequestRepository->findById($modificationRequestId);
    if (!$modificationRequest) {
      throw new \DomainException('修正申請が見つかりません');
    }

    if ($modificationRequest->getStatus()->value() !== Status::PENDING) {
      throw new \DomainException('承認待ちの修正申請のみ承認できます');
    }

    // 勤怠記録を更新
    $attendance = $this->attendanceRepository->findById($modificationRequest->getAttendanceId());
    if ($attendance) {
      if ($modificationRequest->getRequestedStartTime()) {
        $attendance->setStartTime($modificationRequest->getRequestedStartTime());
      }
      if ($modificationRequest->getRequestedEndTime()) {
        $attendance->setEndTime($modificationRequest->getRequestedEndTime());
      }
      $attendance->setRemarks($modificationRequest->getRequestedRemarks());

      $this->attendanceRepository->save($attendance);
    }

    // 修正申請を承認済みに更新
    $modificationRequest->setStatus(new Status(Status::APPROVED));
    $modificationRequest->setApprovedBy(Auth::id());
    $modificationRequest->setApprovedAt(now()->format('Y-m-d H:i:s'));

    $this->modificationRequestRepository->save($modificationRequest);

    return $modificationRequest;
  }
}
