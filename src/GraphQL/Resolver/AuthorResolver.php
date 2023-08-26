<?php

namespace App\GraphQL\Resolver;

use App\Dto\Input\AuthorsFiltersDto;
use App\Dto\Output\AuthorDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthorResolver
{
    private AuthorRepositoryInterface $authors;
    private SerializerInterface $serializer;

    public function __construct(
        AuthorRepositoryInterface $authors,
        SerializerInterface $serializer
    )
    {
        $this->authors = $authors;
        $this->serializer = $serializer;
    }

    public function author(int $id): ?AuthorDto
    {
        $author = $this->authors->findById($id);

        return $author ? AuthorDto::fromAuthorEntity($author) : null;
    }

    public function authors(array $filters): array
    {
        $filters = $this->serializer->deserialize(json_encode($filters), AuthorsFiltersDto::class, 'json');

        return array_map(fn(Author $author) => AuthorDto::fromAuthorEntity($author), $this->authors->findAuthors($filters));
    }
}