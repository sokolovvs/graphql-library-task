<?php

namespace App\GraphQL\Mutator;

use App\Dto\NewAuthorDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorMutator
{
    private AuthorRepositoryInterface $authors;
    private ValidatorInterface $validator;

    public function __construct(AuthorRepositoryInterface $authors, ValidatorInterface $validator)
    {
        $this->authors = $authors;
        $this->validator = $validator;
    }

    public function create(ArgumentInterface $argument): Author|UserError
    {
        $dto = new NewAuthorDto($argument['author']['name'] ?? '');
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError((string)$violationList);
        }
        $author = new Author($dto->name);
        $this->authors->save($author);

        return $author;
    }
}