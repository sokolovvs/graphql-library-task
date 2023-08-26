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
}