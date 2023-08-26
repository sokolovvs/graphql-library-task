<?php

namespace App\Tests\GraphQL;

trait BookTestTrait
{
    private function createBookMutation(string $name, string $description, string $publicationDate, array $authors): array
    {
        $authors = implode(',', $authors);
        return [
            "query" => "mutation createBook {
  createBook(book: {name: \"$name\", description: \"$description\", publicationDate: \"$publicationDate\", authors: [$authors]}) {
    id
  }
}
",
            "variables" => null,
            "operationName" => "createBook"
        ];
    }

    private function queryBookById(int $id): array
    {
        return [
            "query" => "query GetBook {
  book (id: $id) {
    id,
    name,
    description,
    publicationDate,
    authors {
        id
    }
  }
}
",
            "variables" => null,
            "operationName" => "GetBook"
        ];
    }

    private function queryBooks(): array
    {
        return [
            "query" => "query GetBooks {
  books {
    id,
    name,
    publicationDate
  }
}
",
            "variables" => null,
            "operationName" => "GetBooks"
        ];
    }

    private function deleteBookByIdMutation(int $id): array
    {
        return [
            "query" => "mutation DeleteBook {
  deleteBook(id: $id)
}
",
            "variables" => null,
            "operationName" => "DeleteBook"
        ];
    }

    private function editBookByIdMutation(int $bookId, string $name, string $description, string $publicationDate, array $authors): array
    {
        $authors = implode(',', $authors);

        return [
            "query" => "mutation EditBook {
  editBook(id: $bookId, book: {name: \"$name\", description: \"$description\", publicationDate: \"$publicationDate\", authors: [$authors]}) {
    id
    name
    description
    publicationDate
  }
}
",
            "variables" => null,
            "operationName" => "EditBook"
        ];
    }
}