<?php

namespace App\Domain\ModificationRequest\Repositories;

interface ModificationRequestInterface
{
    public function findAll(string $searchTerm): array;
}
