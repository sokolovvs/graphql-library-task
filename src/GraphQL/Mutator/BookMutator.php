<?php

namespace App\GraphQL\Mutator;

use App\Dto\BookDto;
use App\Entity\Author;
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

    public function createBook(ArgumentInterface $argument): Book|UserError
    {
        $dto = new BookDto(
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

    public function editBook(ArgumentInterface $argument): Book|UserError
    {
        $book = $this->books->findById($id = $argument['id'] ?? -1);
        if ($book === null) {
            return new UserError("Unknown book#$id");
        }

        $dto = new BookDto(
            $argument['book']['name'] ?? '',
            $argument['book']['authors'] ?? [],
            $argument['book']['description'] ?? null,
            $argument['book']['publicationDate'] ?? '',
        );
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }

        $existedAuthors = $book->getAuthors()->map(fn(Author $author) => $author->getId())->toArray();
        foreach ($existedAuthors as $id) {
            if (!in_array($id, $dto->authors)) {
                $book->removeAuthor($this->authors->findById($id));
            }
        }
        foreach ($dto->authors as $id) {
            if (!in_array($id, $existedAuthors)) {
                $book->addAuthor($this->authors->findById($id));
            }
        }
        $book->updateName($dto->name)
            ->updateDescription($dto->description)
            ->updatePublicationDate(new DateTimeImmutable($dto->publicationDate));

        $this->books->save($book);

        return $book;
    }

    public function deleteBook(ArgumentInterface $argument): bool|UserError
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