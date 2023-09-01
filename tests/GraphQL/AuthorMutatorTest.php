<?php

namespace App\Tests\GraphQL;

use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorMutatorTest extends WebTestCase
{
    use AuthorTestTrait;

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
     * @dataProvider createAuthorFailCases
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

    public function createAuthorFailCases(): iterable
    {
        yield 'input error if name is too short' => [' S   ', 'This value is too short. It should have 2 characters or more.' . PHP_EOL];
        yield 'input error if name is too long' => [str_repeat('A', 1222), 'This value is too long. It should have 128 characters or less.' . PHP_EOL];
    }

    public function testUpdateAuthor(): void
    {
        $author = $this->addAuthor('Jack Sparrow');
        $this->httpClient->request(Request::METHOD_POST, '/', $this->updateAuthorMutation($author->getId(), $expectedName = 'Jack Falcon'));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $newName = $decodedResponse['data']['editAuthor']['name'] ?? '';
        self::assertEquals($expectedName, $newName);
    }

    public function testUpdateAuthorFailedIfUnknownAuthor(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', $this->updateAuthorMutation(-1, 'Philip Roth'));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualMessage = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals('Unknown author #-1', $actualMessage);
    }

    public function testUpdateAuthorFailedIfNameIsInvalid(): void
    {
        $author = $this->addAuthor('Erica Jong');
        $this->httpClient->request(Request::METHOD_POST, '/', $this->updateAuthorMutation($author->getId(), ' E \n'));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualMessage = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals('This value is too short. It should have 2 characters or more.' . PHP_EOL, $actualMessage);
    }

    public function testDeleteUnknownAuthor(): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteAuthorMutation(-1));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualMessage = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals('Unknown author #-1', $actualMessage);
    }

    public function testDeleteAuthor(): void
    {
        $author = $this->addAuthor('Erica H.');
        $author2 = $this->addAuthor('Schindler M');
        $book = new Book('SGU', 'some description', new DateTimeImmutable('2002-05-09'), ...[$author, $author2]);
        $this->em->persist($book);
        $this->em->flush();
        $bookId = $book->getId();

        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteAuthorMutation($authorId = $author->getId()));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        self::assertEquals([
            "data" => [
                "deleteAuthor" => true
            ]
        ], $decodedResponse);
        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteAuthorMutation($authorId));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualMessage = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("Unknown author #$authorId", $actualMessage);

        $this->httpClient->request(Request::METHOD_POST, '/', $this->deleteAuthorMutation($author2Id = $author2->getId()));
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $actualMessage = $decodedResponse['errors'][0]['message'] ?? '';
        self::assertEquals("Can not remove author#$author2Id because book#$bookId has not another author. Book must has min 1 author", $actualMessage);
    }
}