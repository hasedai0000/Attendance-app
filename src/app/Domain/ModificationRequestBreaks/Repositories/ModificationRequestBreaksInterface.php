<?php

namespace App\Domain\ModificationRequestBreaks\Repositories;

interface ModificationRequestBreaksInterface
{
    public function findAll(string $searchTerm): array;
}
