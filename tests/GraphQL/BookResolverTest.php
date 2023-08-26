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

class BookResolverTest extends WebTestCase
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
}