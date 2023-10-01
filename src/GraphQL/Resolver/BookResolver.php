<?php

namespace App\GraphQL\Resolver;

use App\Dto\Input\BooksFiltersDto;
use App\Dto\Output\BookDto;
use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;

final class BookResolver
{
    private BookRepositoryInterface $books;
    private SerializerInterface $serializer;

    public function __construct(
        BookRepositoryInterface $books,
        SerializerInterface $serializer
    )
    {
        $this->books = $books;
        $this->serializer = $serializer;
    }

    public function book(int $id): ?BookDto
    {
        $book = $this->books->findById($id);

        return $book ? BookDto::fromBookEntity($book) : null;
    }

    public function books(array $filters): array|UserError
    {
        $filters = $this->serializer->deserialize(json_encode($filters), BooksFiltersDto::class, 'json');

        return array_map(fn(Book $book) => BookDto::fromBookEntity($book), $this->books->findBooks($filters));
    }

    public function countBooks(array $filters): int|UserError
    {
        $filters = $this->serializer->deserialize(json_encode($filters), BooksFiltersDto::class, 'json');

        return $this->books->countBooks($filters);
    }
}