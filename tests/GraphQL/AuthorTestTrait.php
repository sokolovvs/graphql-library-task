<?php

namespace App\Tests\GraphQL;

use App\Entity\Author;

trait AuthorTestTrait
{
    private function addAuthor(string $name): Author
    {
        $this->em->persist($author = new Author($name));
        $this->em->flush();

        return $author;
    }

    public static function authorByIdQuery(Author|int $author): string
    {
        $id = $author instanceof Author ? $author->getId() : $author;
        return "query {
  author(id: $id) {
    name,
    numberBooks
  }
}";
    }

    private static function authorsQuery(array $filters = []): string
    {
        $inlineFilters = self::inlineFilters($filters);
        return "query {
  authors $inlineFilters {
    name,
    numberBooks
  }
}";
    }

    private static function inlineFilters(array $filters = []): string {
        $inlineFilters = '';
        if (!empty($filters)) {
            $inlineFilters = '{';
            foreach ($filters as $key => $value) {
                $value = is_numeric($value) ? $value : "\"$value\"";
                $inlineFilters .= "$key: $value";
            }
            $inlineFilters .= '}';
            $inlineFilters = "(filters: $inlineFilters)";
        }

        return $inlineFilters;
    }

    private static function authorsCountQuery(array $filters = []): string
    {
        $inlineFilters = self::inlineFilters($filters);
        return "query {
  countAuthors $inlineFilters
}";
    }

    private function createAuthorMutation(string $name): array
    {
        return [
            "query" => "mutation CreateAuthor {
  createAuthor(author: {name: \"$name\"}){
    id
  }
}",
            "variables" => null,
            "operationName" => "CreateAuthor"
        ];
    }

    private function updateAuthorMutation(int $id, string $name): array
    {
        return [
            "query" => "mutation EditAuthor {
  editAuthor(id: $id, author: {name: \"$name\"}){
    id,
    name
  }
}",
            "variables" => null,
            "operationName" => "EditAuthor"
        ];
    }

    private function deleteAuthorMutation(int $id): array
    {
        return [
            "query" => "mutation deleteAuthor {
  deleteAuthor(id: $id)
}",
            "variables" => null,
            "operationName" => "deleteAuthor"
        ];
    }
}