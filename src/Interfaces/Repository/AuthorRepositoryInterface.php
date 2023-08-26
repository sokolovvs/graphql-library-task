<?php

namespace App\Interfaces\Repository;

use App\Dto\Input\AuthorsFiltersDto;
use App\Entity\Author;

interface AuthorRepositoryInterface
{
    public function findById(int $id): ?Author;

    /**
     * @return array<int, Author>
     */
    public function findAuthors(AuthorsFiltersDto $filters): array;

    public function save(Author $author): void;

    public function remove(Author $author): void;
}