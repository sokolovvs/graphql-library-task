<?php

namespace App\GraphQL\Mutator;

use App\Dto\Input\BookDto as BookInputDto;
use App\Dto\Output\BookDto as BookOutputDto;
use App\Entity\Author;
use App\Entity\Book;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use App\Interfaces\Repository\BookRepositoryInterface;
use DateTimeImmutable;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Serializer\SerializerInterface;

final class BookMutator
{
    private BookRepositoryInterface $books;
    private AuthorRepositoryInterface $authors;
    private SerializerInterface $serializer;

    public function __construct(
        BookRepositoryInterface   $books,
        AuthorRepositoryInterface $authors,
        SerializerInterface $serializer
    )
    {
        $this->books = $books;
        $this->authors = $authors;
        $this->serializer = $serializer;
    }

    public function createBook(array $data): BookOutputDto|UserError
    {
        $dto = $this->serializer->deserialize(json_encode($data), BookInputDto::class, 'json');
        $authors = [];
        foreach ($dto->authors as $id) {
            $authors[] = $this->authors->findById($id);
        }
        $book = new Book(
            $dto->name,
            $dto->description,
            new DateTimeImmutable($dto->publicationDate),
            ...$authors
        );
        $this->books->save($book);

        return BookOutputDto::fromBookEntity($book);
    }

    public function editBook(int $id, array $data): BookOutputDto|UserError
    {
        $book = $this->books->findById($id);
        if ($book === null) {
            return new UserError("Unknown book#$id");
        }

        $dto = $this->serializer->deserialize(json_encode($data), BookInputDto::class, 'json');

        $existedAuthors = $book->getAuthors()->map(fn(Author $author) => $author->getId())->toArray();
        foreach ($existedAuthors as $id) {
            if (!in_array($id, $dto->authors)) {
                $book->removeAuthor($this->authors->findById($id));
            }
        }
        foreach ($dto->authors as $id) {
            if (!in_array($id, $existedAuthors)) {
                $book->addAuthor($this->authors->findById($id));
            }
        }
        $book->updateName($dto->name)
            ->updateDescription($dto->description)
            ->updatePublicationDate(new DateTimeImmutable($dto->publicationDate));

        $this->books->save($book);

        return BookOutputDto::fromBookEntity($book);
    }

    public function deleteBook(int $id): bool|UserError
    {
        $book = $this->books->findById($id);
        if ($book === null) {
            return new UserError("Unknown book#$id");
        }
        foreach ($book->getAuthors() as $author) {
            $author->removeBook($book);
        }

        $this->books->remove($book);

        return true;
    }
}