<?php

namespace App\Infrastructure\Repositories;

use App\Domain\ModificationRequestBreaks\Repositories\ModificationRequestBreaksInterface;

class EloquentModificationRequestBreaksRepository implements ModificationRequestBreaksInterface
{
    public function findAll(string $searchTerm): array
    {
        // TODO: 実装を追加
        return [];
    }
}
