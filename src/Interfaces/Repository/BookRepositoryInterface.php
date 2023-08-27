<?php

namespace App\Interfaces\Repository;

use App\Dto\Input\BooksFiltersDto;
use App\Entity\Book;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;

    /**
     * @return array<int, Book>
     */
    public function findBooks(BooksFiltersDto $filters): array;

    public function save(Book $book): void;

    public function remove(Book $book): void;
}