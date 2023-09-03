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

    private static ?int $totalBooks = null;

    /**
     * @dataProvider dataProviderBooksFilter
     */
    public function testBooksFilter(array $request, callable $postCondition): void
    {
        if (self::$totalBooks === null) {
            self::$totalBooks = (int)$this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM books');
            $this->loadBooks();
        }
        $this->httpClient->request(Request::METHOD_POST, '/', $request);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $postCondition($decodedResponse);
    }

    public function dataProviderBooksFilter(): iterable
    {
        yield 'items w/o filters' => [self::queryBooks(), [$this, 'assertBooksListStructure']];
        yield 'count items w/o filters' => [
            self::countBooks(), function (array $decodedResponse) {
                $actualCount = $decodedResponse['data']['countBooks'] ?? -1;
                self::assertEquals(self::$totalBooks + 7, $actualCount);
            }
        ];
        $filters = [
            'name' => 'Geek',
            'description' => 'This book',
            'minPublicationDate' => '2023-04-02',
            'maxPublicationDate' => '2023-06-01'
        ];
        yield 'items all filters' => [
            self::queryBooks($filters),
            function (array $decodedResponse) {
                $books = $decodedResponse['data']['books'] ?? [];
                self::assertCount(2, $books);
                self::assertBooksListStructure($decodedResponse);
            }
        ];
        yield 'count all filters' => [
            self::countBooks($filters),
            function (array $decodedResponse) {
                $actualCount = $decodedResponse['data']['countBooks'] ?? -1;
                self::assertEquals(2, $actualCount);
            }
        ];
        unset($filters['name']);
        yield 'error if try to filter items w/o name' => [
            self::queryBooks($filters),
            function (array $decodedResponse) {
                $error = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("Invalid searching criteria is invalid. You can't search by other fields without name.\n", $error);
            }
        ];
        yield 'error if try to count w/ filter w/o name' => [
            self::countBooks($filters),
            function (array $decodedResponse) {
                $error = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("Invalid searching criteria is invalid. You can't search by other fields without name.\n", $error);
            }
        ];
    }

    private function loadBooks(): void
    {
        $this->em->persist($author = new Author('Katherine Dunn'));
        $this->em->persist($author2 = new Author('Helen Dunn'));
        $this->em->flush();
        $this->em->persist(new Book('Geek Love', 'This book for geeks', new DateTimeImmutable('2023-05-01'), $author));
        $this->em->persist(new Book('Geek Mind', 'This book for geeks', new DateTimeImmutable('2023-04-01'), $author));
        $this->em->persist(new Book('Mind', 'This book ...', new DateTimeImmutable('2023-06-01'), $author));
        $this->em->persist(new Book('Geek & Deep mind', 'This book ...', new DateTimeImmutable('2023-06-01'), $author));
        $this->em->persist(new Book('Mind 2', 'This book ...', new DateTimeImmutable('2023-06-01'), $author2));
        $this->em->persist(new Book('Unknown geek', 'This book ...', new DateTimeImmutable('2023-06-01'), $author2));
        $this->em->persist(new Book('Geek', null, new DateTimeImmutable('2023-05-01'), $author));
        $this->em->flush();
    }

    private static function assertBooksListStructure(array $decodedResponse): void
    {
        $books = $decodedResponse['data']['books'] ?? [];
        foreach ($books as $book) {
            self::assertArrayHasKey('id', $book);
            self::assertIsNumeric($book['id']);
            self::assertArrayHasKey('name', $book);
            self::assertIsString($book['name']);
            self::assertArrayHasKey('publicationDate', $book);
            self::assertIsString($book['publicationDate']);
            self::assertArrayHasKey('authors', $book);
            self::assertIsArray($book['authors']);
        }
    }
}