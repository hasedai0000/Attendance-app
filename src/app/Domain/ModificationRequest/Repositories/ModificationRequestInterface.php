<?php

namespace App\Domain\ModificationRequest\Repositories;

interface ModificationRequestRepositoryInterface
{
    public function findAll(string $searchTerm): array;
}
