<?php

namespace App\GraphQL\Resolver;

use App\Dto\Output\AuthorDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;

final class AuthorResolver
{
    private AuthorRepositoryInterface $authors;

    public function __construct(AuthorRepositoryInterface $authors)
    {
        $this->authors = $authors;
    }

    public function author(int $id): ?AuthorDto
    {
        $author = $this->authors->findById($id);

        return $author ? AuthorDto::fromAuthorEntity($author) : null;
    }

    public function authors(): array
    {
        return array_map(fn(Author $author) => AuthorDto::fromAuthorEntity($author), $this->authors->findAllAuthors());
    }
}