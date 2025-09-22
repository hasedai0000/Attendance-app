<?php

namespace App\Domain\ModificationRequestBreaks\Repositories;

interface ModificationRequestBreaksRepositoryInterface
{
    public function findAll(string $searchTerm): array;
}
