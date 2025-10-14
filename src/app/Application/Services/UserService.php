<?php

namespace App\Application\Services;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * ユーザーを取得
     */
    public function getUser(string $userId): ?UserEntity
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * ユーザーを取得（Eloquentモデル）
     */
    public function getUserById(string $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * 全ユーザーを取得
     */
    public function getAllUsers(): array
    {
        return User::where('is_admin', false)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
