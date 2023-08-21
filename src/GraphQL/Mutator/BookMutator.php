<?php

namespace App\GraphQL\Mutator;

use App\Dto\CreateBookDto;
use App\Entity\Book;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use App\Interfaces\Repository\BookRepositoryInterface;
use App\Interfaces\Service\ErrorFormatterInterface;
use DateTimeImmutable;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookMutator
{
    private BookRepositoryInterface $books;
    private ValidatorInterface $validator;
    private ErrorFormatterInterface $errorFormatter;
    private AuthorRepositoryInterface $authors;

    public function __construct(
        BookRepositoryInterface   $books,
        AuthorRepositoryInterface $authors,
        ValidatorInterface        $validator,
        ErrorFormatterInterface   $errorFormatter
    )
    {
        $this->books = $books;
        $this->authors = $authors;
        $this->validator = $validator;
        $this->errorFormatter = $errorFormatter;
    }

    public function create(ArgumentInterface $argument): Book|UserError
    {
        $dto = new CreateBookDto(
            $argument['book']['name'] ?? '',
            $argument['book']['authors'] ?? [],
            $argument['book']['description'] ?? null,
            $argument['book']['publicationDate'] ?? '',
        );
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }
        $authors = [];
        foreach ($dto->authors as $id) {
            $authors[] = $this->authors->findById($id);
        }
        $book = new Book(
            $dto->name,
            $dto->description,
            new DateTimeImmutable($dto->publicationDate),
            ...$authors
        );
        $this->books->save($book);

        return $book;
    }

    public function delete(ArgumentInterface $argument): bool|UserError
    {
        $id = (int)$argument['id'];

        $book = $this->books->findById($id);
        if ($book === null) {
            return new UserError("Unknown book#$id");
        }
        foreach ($book->getAuthors() as $author) {
            $author->removeBook($book);
        }

        $this->books->remove($book);

        return true;
    }
}