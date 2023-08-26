<?php

namespace App\GraphQL\Mutator;

use App\Dto\Input\AuthorDto as AuthorInputDto;
use App\Dto\Output\AuthorDto as AuthorOutputDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use App\Interfaces\Service\ErrorFormatterInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthorMutator
{
    private AuthorRepositoryInterface $authors;
    private ValidatorInterface $validator;
    private ErrorFormatterInterface $errorFormatter;
    private SerializerInterface $serializer;

    public function __construct(
        AuthorRepositoryInterface $authors,
        ValidatorInterface        $validator,
        ErrorFormatterInterface   $errorFormatter,
        SerializerInterface       $serializer
    )
    {
        $this->authors = $authors;
        $this->validator = $validator;
        $this->errorFormatter = $errorFormatter;
        $this->serializer = $serializer;
    }

    public function createAuthor(array $data): AuthorOutputDto|UserError
    {
        $dto = $this->serializer->deserialize(json_encode($data), AuthorInputDto::class, 'json');
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }
        $author = new Author($dto->name);
        $this->authors->save($author);

        return AuthorOutputDto::fromAuthorEntity($author);
    }

    public function editAuthor(int $id, array $data): AuthorOutputDto|UserError
    {
        $author = $this->authors->findById($id);
        if ($author === null) {
            return new UserError("Unknown author #$id");
        }
        $dto = $this->serializer->deserialize(json_encode($data), AuthorInputDto::class, 'json');
        $violationList = $this->validator->validate($dto);
        if ($violationList->count()) {
            return new UserError($this->errorFormatter->format($violationList));
        }
        $author->updateName($dto->name);
        $this->authors->save($author);

        return AuthorOutputDto::fromAuthorEntity($author);
    }

    public function deleteAuthor(int $id): bool|UserError
    {
        $author = $this->authors->findById($id);
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