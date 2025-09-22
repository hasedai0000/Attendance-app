<?php

namespace App\Domain\Breaks\Repositories;

interface BreaksRepositoryInterface
{
    public function findAll(string $searchTerm): array;
}
