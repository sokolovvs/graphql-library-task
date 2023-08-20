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
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
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
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $firstAuthor = $decodedResponse['data']['authors'][0] ?? [];
        self::assertEquals([
            "name" => "Suzanne Collins",
            "numberBooks" => 0
        ], $firstAuthor);
    }

    public function testCreateAuthor(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', $this->createAuthorMutation('Jeremy Parker'));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $id = $decodedResponse['data']['createAuthor']['id'] ?? -1;


        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorByIdQuery($id),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
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

    /**
     * @dataProvider authorFailCases
     */
    public function testCreateAuthorFailCases(string $name, string $expectedMessage): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', $this->createAuthorMutation($name));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals($expectedMessage, $message);
    }

    public function authorFailCases(): iterable
    {
        yield 'input error if name is too short' => [' S   ', 'This value is too short. It should have 2 characters or more.' . PHP_EOL];
        yield 'input error if name is too long' => [str_repeat('A', 1222), 'This value is too long. It should have 128 characters or less.' . PHP_EOL];
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