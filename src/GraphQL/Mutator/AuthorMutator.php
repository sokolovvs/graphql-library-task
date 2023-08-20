<?php

namespace App\GraphQL\Mutator;

use App\Dto\AuthorDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use App\Interfaces\Service\ErrorFormatterInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorMutator
{
    private AuthorRepositoryInterface $authors;
    private ValidatorInterface $validator;
    private ErrorFormatterInterface $errorFormatter;

    public function __construct(
        AuthorRepositoryInterface $authors,
        ValidatorInterface        $validator,
        ErrorFormatterInterface   $errorFormatter
    )
    {
        $this->authors = $authors;
        $this->validator = $validator;
        $this->errorFormatter = $errorFormatter;
    }

    public function create(ArgumentInterface $argument): Author|UserError
    {
        $dto = new AuthorDto($argument['author']['name'] ?? '');
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }
        $author = new Author($dto->name);
        $this->authors->save($author);

        return $author;
    }

    public function edit(ArgumentInterface $argument): Author|UserError
    {
        $author = $this->authors->findById($id = $argument['id'] ?? -1);
        if ($author === null) {
            return new UserError("Unknown author #$id");
        }
        $dto = new AuthorDto($argument['author']['name'] ?? '');
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }
        $author->updateName($dto->name);

        return $author;
    }

    public function delete(ArgumentInterface $argument): bool|UserError
    {
        $author = $this->authors->findById($id = $argument['id'] ?? -1);
        if ($author === null) {
            return new UserError("Unknown author #$id");
        }
        foreach ($author->getBooks() as $book) {
            if ($book->getAuthors() < 2) {
                return new UserError("Can not remove author#{$author->getId()} because book#{$book->getId()} has not another author. Book must has min 1 author");
            }
        }
        $this->authors->remove($author);

        return true;
    }
}