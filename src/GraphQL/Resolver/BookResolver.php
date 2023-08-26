<?php

namespace App\GraphQL\Resolver;

use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;

final class BookResolver
{
    private BookRepositoryInterface $books;

    public function __construct(BookRepositoryInterface $books)
    {
        $this->books = $books;
    }

    public function book(int $id): ?Book
    {
        return $this->books->findById($id);
    }

    public function books(): array
    {
        return $this->books->findAllBooks();
    }
}