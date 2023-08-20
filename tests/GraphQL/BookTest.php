<?php

namespace App\Tests\GraphQL;

use App\Entity\Author;
use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookTest extends WebTestCase
{
    private const BOOK_DESCRIPTION = <<<BD
Alice's Adventures in Wonderland (commonly Alice in Wonderland) is an 1865 English children's novel by Lewis Carroll, a mathematics don at Oxford University. It details the story of a young girl named Alice who falls through a rabbit hole into a fantasy world of anthropomorphic creatures. It is seen as an example of the literary nonsense genre. The artist John Tenniel provided 42 wood-engraved illustrations for the book.
BD;

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

    public function testCreateBook(): void
    {
        $this->em->persist($author = new Author('Lewis Carroll'));
        $this->em->flush();
        $name = 'Alice in Wonderland';
        $description = self::BOOK_DESCRIPTION;
        $this->httpClient->request(Request::METHOD_POST, '/', $this->createBookMutation($name, $description, '1865-01-01', [$authorId = $author->getId()]));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $id = $decodedResponse['data']['createBook']['id'] ?? -1;


        $this->httpClient->request(Request::METHOD_POST, '/', $this->queryBookById($id));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        self::assertEquals([
            "data" => [
                "book" => [
                    "id" => $id,
                    "name" => $name,
                    "description" => $description,
                    "publicationDate" => "1865-01-01"
                ]
            ]
        ], $decodedResponse);


        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => AuthorTest::authorByIdQuery($authorId),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $numberBooks = $decodedResponse['data']['author']['numberBooks'] ?? -1;
        self::assertEquals(1, $numberBooks);
    }

    public function testCreateBookFailed(): void
    {
        $name = ' A   \n';
        $description = self::BOOK_DESCRIPTION;
        $this->httpClient->request(Request::METHOD_POST, '/', $this->createBookMutation($name, str_repeat($description, 10), '0 BC', [-512]));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("This value is too short. It should have 2 characters or more." . PHP_EOL .
            "This value is too long. It should have 1024 characters or less." . PHP_EOL .
            "This value is not a valid date." . PHP_EOL .
            "Invalid authorId" . PHP_EOL, $message);
    }

    public function testBooks(): void
    {
        $this->em->persist($author = new Author('Katherine Dunn'));
        $this->em->flush();
        $this->em->persist($book = new Book('Geek Love', self::BOOK_DESCRIPTION, new DateTimeImmutable('1996-05-01'), $author));
        $this->em->flush();
        $this->httpClient->request(Request::METHOD_POST, '/', $this->queryBooks());
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $books = $decodedResponse['data']['books'] ?? [];
        foreach ($books as $book) {
            self::assertArrayHasKey('id', $book);
            self::assertIsNumeric($book['id']);
            self::assertArrayHasKey('name', $book);
            self::assertIsString($book['name']);
            self::assertArrayHasKey('publicationDate', $book);
            self::assertIsString($book['publicationDate']);
        }
    }

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
    publicationDate
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
}