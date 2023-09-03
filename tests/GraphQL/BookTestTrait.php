<?php

namespace App\Tests\GraphQL;

use App\Tests\Utils\GraphQLUtil;

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

    private static function queryBooks(array $filters = []): array
    {
        $filters = GraphQLUtil::inlineFilters($filters);
        return [
            "query" => "query GetBooks {
  books $filters {
    id,
    name,
    publicationDate
    authors {
        id,
        name
    }
  }
}
",
            "variables" => null,
            "operationName" => "GetBooks"
        ];
    }

    private static function countBooks(array $filters = []): array
    {
        $filters = GraphQLUtil::inlineFilters($filters);
        return [
            "query" => "query countBooks {
  countBooks $filters
}
",
            "variables" => null,
            "operationName" => "countBooks"
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
    authors {
        id,
        name
    }
  }
}
",
            "variables" => null,
            "operationName" => "EditBook"
        ];
    }
}