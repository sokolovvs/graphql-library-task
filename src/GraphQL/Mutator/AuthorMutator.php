<?php

namespace App\GraphQL\Mutator;

use App\Dto\Input\AuthorDto as AuthorInputDto;
use App\Dto\Output\AuthorDto as AuthorOutputDto;
use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthorMutator
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

    public function createAuthor(array $data): AuthorOutputDto|UserError
    {
        $dto = $this->serializer->deserialize(json_encode($data), AuthorInputDto::class, 'json');
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
            if ($book->getAuthors()->count() < 2) {
                return new UserError("Can not remove author#{$author->getId()} because book#{$book->getId()} has not another author. Book must has min 1 author");
            }
        }
        $this->authors->remove($author);

        return true;
    }
}