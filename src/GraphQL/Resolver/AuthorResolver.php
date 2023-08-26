<?php

namespace App\GraphQL\Resolver;

use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;

final class AuthorResolver
{
    private AuthorRepositoryInterface $authors;

    public function __construct(AuthorRepositoryInterface $authors)
    {
        $this->authors = $authors;
    }

    public function author(int $id): ?Author
    {
        return $this->authors->findById($id);
    }

    public function authors(): array
    {
        return $this->authors->findAllAuthors();
    }

    public function __invoke(ResolveInfo $info, ArgumentInterface $arguments)
    {
        return $this->{$info->fieldName}($arguments);
    }
}