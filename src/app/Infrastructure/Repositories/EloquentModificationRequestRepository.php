<?php

namespace App\Infrastructure\Repositories;

use App\Domain\ModificationRequest\Repositories\ModificationRequestInterface;

class EloquentModificationRequestRepository implements ModificationRequestInterface
{
    public function findAll(string $searchTerm): array
    {
        // TODO: 実装を追加
        return [];
    }
}
