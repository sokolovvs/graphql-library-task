<?php

namespace App\Tests\GraphQL;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorResolverTest extends WebTestCase
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
        $firstAuthorExpected = $this->em->getConnection()
            ->fetchAllAssociative('SELECT name, number_books FROM authors ORDER BY id ASC LIMIT 1')[0] ?? [];
        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorsQuery(),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $firstAuthorActual = $decodedResponse['data']['authors'][0] ?? [];
        self::assertEquals([
            'name' => $firstAuthorExpected['name'] ?? '',
            'numberBooks' => $firstAuthorExpected['number_books'] ?? -1,
        ], $firstAuthorActual);
    }

    public function testFilterAuthors(): void
    {
        $this->addAuthor('Feliks An');
        $this->addAuthor('Fernando Aguero');
        $this->addAuthor('Alex');
        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorsQuery(['name' => 'Fe']),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $authors = $decodedResponse['data']['authors'] ?? [];
        self::assertCount(2, $authors);

        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $this->authorsCountQuery(['name' => 'Fe']),
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $authorsQty = $decodedResponse['data']['countAuthors'] ?? -1;
        self::assertEquals(2, $authorsQty);
    }

    /**
     * @dataProvider dataProviderAuthorsFilterFailCases
     */
    public function testAuthorsFilterFailCases(string $request, callable $postCondition): void
    {
        $this->httpClient->request(Request::METHOD_POST, '/', [
            'query' => $request,
            'variables' => null,
        ]);
        $response = $this->httpClient->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $decodedResponse = json_decode($response->getContent(), true);
        $postCondition($decodedResponse);
    }

    public function dataProviderAuthorsFilterFailCases(): iterable
    {
        yield '<authors> limit can not be greater than 200' => [
            self::authorsQuery(['limit' => 201],), function (array $decodedResponse) {
                $actual = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("This value should be less than or equal to 200.\n", $actual);
            }
        ];

        yield '<authors> limit can not be less than 5' => [
            self::authorsQuery(['limit' => 4],), function (array $decodedResponse) {
                $actual = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("This value should be greater than or equal to 5.\n", $actual);
            }
        ];

        yield '<authors> page can not be less than 1' => [
            self::authorsQuery(['page' => -1],), function (array $decodedResponse) {
                $actual = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("This value should be greater than or equal to 1.\n", $actual);
            }
        ];

        yield '<authors> composite' => [
            self::authorsQuery(['page' => -111111, 'limit' => 2011],), function (array $decodedResponse) {
                $actual = $decodedResponse['errors'][0]['message'] ?? '';
                self::assertEquals("This value should be greater than or equal to 1.\nThis value should be less than or equal to 200.\n", $actual);
            }
        ];
    }
}