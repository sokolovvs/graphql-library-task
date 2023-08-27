<?php

namespace App\GraphQL\Resolver;

use App\Dto\Input\BooksFiltersDto;
use App\Dto\Output\BookDto;
use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;
use App\Interfaces\Service\ErrorFormatterInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BookResolver
{
    private BookRepositoryInterface $books;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private ErrorFormatterInterface $errorFormatter;

    public function __construct(
        BookRepositoryInterface $books,
        SerializerInterface     $serializer,
        ValidatorInterface      $validator,
        ErrorFormatterInterface $errorFormatter
    )
    {
        $this->books = $books;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->errorFormatter = $errorFormatter;
    }

    public function book(int $id): ?BookDto
    {
        $book = $this->books->findById($id);

        return $book ? BookDto::fromBookEntity($book) : null;
    }

    public function books(array $filters): array|UserError
    {
        $filters = $this->serializer->deserialize(json_encode($filters), BooksFiltersDto::class, 'json');
        $violationList = $this->validator->validate($filters);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }

            return array_map(fn(Book $book) => BookDto::fromBookEntity($book), $this->books->findBooks($filters));
    }
}