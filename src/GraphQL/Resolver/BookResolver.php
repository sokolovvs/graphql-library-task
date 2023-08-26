<?php

namespace App\GraphQL\Resolver;

use App\Dto\Output\BookDto;
use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;

final class BookResolver
{
    private BookRepositoryInterface $books;

    public function __construct(BookRepositoryInterface $books)
    {
        $this->books = $books;
    }

    public function book(int $id): ?BookDto
    {
        $book = $this->books->findById($id);

        return $book ? BookDto::fromBookEntity($book) : null;
    }

    public function books(): array
    {
        return array_map(fn(Book $book) => BookDto::fromBookEntity($book), $this->books->findAllBooks());
    }
}