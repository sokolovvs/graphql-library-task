<?php

namespace App\Dto\Output;

use App\Entity\Author;

final class AuthorDto
{
    public readonly int $id;
    public readonly string $name;
    public readonly int $numberBooks;

    public function __construct(int $id, string $name, int $numberBooks)
    {
        $this->id = $id;
        $this->name = $name;
        $this->numberBooks = $numberBooks;
    }

    public static function fromAuthorEntity(Author $author): self
    {
        return new self($author->getId(), $author->getName(), $author->getNumberBooks());
    }
}