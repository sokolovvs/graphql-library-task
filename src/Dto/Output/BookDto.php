<?php

namespace App\Dto\Output;

use App\Entity\Author;
use App\Entity\Book;
use DateTimeImmutable;

final class BookDto
{
    public readonly int $id;
    public readonly string $name;
    public readonly ?string $description;
    public readonly ?string $publicationDate;
    /**
     * @var AuthorDto[]
     */
    public readonly array $authors;

    public function __construct(int $id, string $name, ?string $description, ?DateTimeImmutable $publicationDate, AuthorDto ...$authors)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->publicationDate = $publicationDate?->format('Y-m-d');
        $this->authors = $authors;
    }

    public static function fromBookEntity(Book $book): self
    {
        return new self(
            $book->getId(),
            $book->getName(),
            $book->getDescription(),
            $book->getPublicationDate(),
            ...array_map(fn(Author $author) => AuthorDto::fromAuthorEntity($author), $book->getAuthors()->toArray())
        );
    }
}