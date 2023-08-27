<?php

namespace App\GraphQL\Resolver;

use App\Dto\Input\AuthorsFiltersDto;
use App\Dto\Output\AuthorDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use App\Interfaces\Service\ErrorFormatterInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthorResolver
{
    private AuthorRepositoryInterface $authors;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private ErrorFormatterInterface $errorFormatter;

    public function __construct(
        AuthorRepositoryInterface $authors,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorFormatterInterface $errorFormatter
    )
    {
        $this->authors = $authors;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->errorFormatter = $errorFormatter;
    }

    public function author(int $id): ?AuthorDto
    {
        $author = $this->authors->findById($id);

        return $author ? AuthorDto::fromAuthorEntity($author) : null;
    }

    public function authors(array $filters): array|UserError
    {
        $filters = $this->serializer->deserialize(json_encode($filters), AuthorsFiltersDto::class, 'json');
        $violationList = $this->validator->validate($filters);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }

        return array_map(fn(Author $author) => AuthorDto::fromAuthorEntity($author), $this->authors->findAuthors($filters));
    }
}