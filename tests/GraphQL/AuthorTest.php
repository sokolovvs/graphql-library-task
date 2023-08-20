<?php

namespace App\Tests\GraphQL;


use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorTest extends WebTestCase
{
    private KernelBrowser $httpClient;

    private TestContainer $testContainer;

    private EntityManagerInterface $em;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpClient = self::createClient();
        $this->testContainer = self::$kernel->getContainer()->get('test.service_container');
        $this->em = $this->testContainer->get(EntityManagerInterface::class);
    }

    public function testGetAuthor(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorByIdQuery($this->addAuthor('Suzanne Collins')),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals($response->getStatusCode(), Response::HTTP_OK);
        $decodedResponse = json_decode($response->getContent(), true);
        self::assertEquals([
            "data" => [
                "author" => [
                    "name" => "Suzanne Collins",
                    "numberBooks" => 0
                ]
            ]
        ], $decodedResponse);
    }

#Naomi Alderman
    public function testGetAuthors(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorsQuery(),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals($response->getStatusCode(), Response::HTTP_OK);
        $decodedResponse = json_decode($response->getContent(), true);
        $firstAuthor = $decodedResponse['data']['authors'][0] ?? [];
        self::assertEquals($firstAuthor, [
            "name" => "Suzanne Collins",
            "numberBooks" => 0
        ]);
    }

    public function testCreateAuthor(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', $this->createAuthorMutation('Jeremy Parker'));
        $response = $this->httpClient->getResponse();
        self::assertEquals($response->getStatusCode(), Response::HTTP_OK);
        $decodedResponse = json_decode($response->getContent(), true);
        $id = $decodedResponse['data']['createAuthor']['id'] ?? -1;


        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorByIdQuery($id),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals($response->getStatusCode(), Response::HTTP_OK);
        $decodedResponse = json_decode($response->getContent(), true);
        self::assertEquals([
            "data" => [
                "author" => [
                    "name" => "Jeremy Parker",
                    "numberBooks" => 0
                ]
            ]
        ], $decodedResponse);
    }

    private function addAuthor(string $name): Author
    {
        $this->em->persist($author = new Author($name));
        $this->em->flush();

        return $author;
    }

    private function authorByIdQuery(Author|int $author): string
    {
        $id = $author instanceof Author ? $author->getId() : $author;
        return "query {
  author(id: $id) {
    name,
    numberBooks
  }
}";
    }

    private function authorsQuery(): string
    {
        return 'query {
  authors {
    name,
    numberBooks
  }
}';
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
}