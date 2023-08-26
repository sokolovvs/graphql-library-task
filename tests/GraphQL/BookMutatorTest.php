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

class BookMutatorTest extends WebTestCase
{
    use BookTestTrait;

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
                    "publicationDate" => "1865-01-01",
                    "authors" => [
                        [
                            "id" => $author->getId()
                        ],
                    ],
                ]
            ]
        ], $decodedResponse);


        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => AuthorResolverTest::authorByIdQuery($authorId),
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

    public function testRemoveBookUnknownBook(): void
    {
        $this->em->persist($author = new Author('Katherine Dunn'));
        $this->em->flush();
        $this->em->persist($book = new Book('Geek Love', self::BOOK_DESCRIPTION, new DateTimeImmutable('1996-05-01'), $author));
        $this->em->flush();
        $id = $book->getId() * (-1);
        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteBookByIdMutation($id));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("Unknown book#$id", $message);
    }

    public function testRemoveBookOk(): void
    {
        $this->em->persist($author = new Author('Katherine Dunn'));
        $this->em->flush();
        $this->em->persist($book = new Book('Geek Love', self::BOOK_DESCRIPTION, new DateTimeImmutable('1996-05-01'), $author));
        $this->em->flush();
        $id = $book->getId();
        $authorId = $author->getId();
        $beforeNumberBooks = $author->getNumberBooks();
        self::assertEquals(1, $beforeNumberBooks);
        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteBookByIdMutation($id));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $result = $decodedResponse['data']['deleteBook'] ?? false;
        self::assertTrue($result);
        $numberBooks = $this->em->getConnection()->fetchOne("select number_books from authors where id=$authorId");
        self::assertEquals($beforeNumberBooks - 1, $numberBooks);

        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteBookByIdMutation($id));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("Unknown book#$id", $message);
    }

    public function testEditBook(): void
    {
        $this->em->persist($author = new Author('Lewis Carroll'));
        $this->em->flush();
        $this->em->persist($author2 = new Author('Lewis Carroll'));
        $this->em->flush();
        $name = 'Alice in Wonderland';
        $description = self::BOOK_DESCRIPTION;
        $request = $this->createBookMutation($name, $description, '1865-01-01', [$authorId = $author->getId(), $author2Id = $author2->getId()]);
        $this->httpClient->request(Request::METHOD_POST, '/', $request);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $id = $decodedResponse['data']['createBook']['id'] ?? -1;

        $name = 'In the Heart of the Sea';
        $description = 'some description';
        $request = $this->editBookByIdMutation($id, $name, $description, '1965-05-12', [$authorId,]);
        $this->httpClient->request(Request::METHOD_POST, '/', $request);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualId = $decodedResponse['data']['editBook']['id'] ?? -1;
        $actualName = $decodedResponse['data']['editBook']['name'] ?? '';
        $actualDescription = $decodedResponse['data']['editBook']['description'] ?? '';
        $actualPublicationDate = $decodedResponse['data']['editBook']['publicationDate'] ?? '';
        self::assertEquals($id, $actualId);
        self::assertEquals($name, $actualName);
        self::assertEquals($description, $actualDescription);
        self::assertEquals('1965-05-12', $actualPublicationDate);

        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => AuthorResolverTest::authorByIdQuery($authorId),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $numberBooks = $decodedResponse['data']['author']['numberBooks'] ?? -1;
        self::assertEquals(1, $numberBooks);

        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => AuthorResolverTest::authorByIdQuery($author2Id),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $numberBooks = $decodedResponse['data']['author']['numberBooks'] ?? -1;
        self::assertEquals(0, $numberBooks);
    }

    public function testErrorIfEditUnknownBook(): void
    {
        $this->em->persist($author = new Author('Lewis Carroll'));
        $this->em->flush();
        $name = 'Alice in Wonderland';
        $description = self::BOOK_DESCRIPTION;
        $request = $this->editBookByIdMutation(-1, $name, $description, '1965-05-12', [$author->getId()]);
        $this->httpClient->request(Request::METHOD_POST, '/', $request);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("Unknown book#-1", $message);
    }

    public function testErrorIfEditDataInvalid(): void
    {
        $this->em->persist($author = new Author('Lewis Carroll'));
        $this->em->flush();
        $this->em->persist($book = new Book('Little women', 'lalala', new DateTimeImmutable('2015-05-26'), $author));
        $this->em->flush();

        $request = $this->editBookByIdMutation($book->getId(), 'X', '', '1666-05-19', [$author->getId()]);
        $this->httpClient->request(Request::METHOD_POST, '/', $request);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $message = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("This value is too short. It should have 2 characters or more.\n", $message);
    }
}