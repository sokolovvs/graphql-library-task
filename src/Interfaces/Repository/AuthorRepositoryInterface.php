<?php

namespace App\Interfaces\Repository;

use App\Entity\Author;

interface AuthorRepositoryInterface
{
    public function findById(int $id): ?Author;

    /**
     * @return array<int, Author>
     */
    public function findAllAuthors(): array;

}