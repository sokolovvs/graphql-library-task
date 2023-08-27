<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Provider\DateTime;
use Faker\Provider\en_US\Text;
use Faker\Provider\Lorem;

class BooksFxiture extends Fixture implements OrderedFixtureInterface
{
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $faker = new Generator();
        $faker->addProvider(new Text($faker));
        $faker->addProvider(new DateTime($faker));
        $faker->addProvider(new Lorem($faker));
        /* @var Connection $connection */
        $connection = $manager->getConnection();
        for ($i = 0; $i < 500; $i++) {
            $authors = [];
            $authorQty = random_int(1, 3);
            $authorsIds = $connection->fetchFirstColumn("select id from authors order by random() limit $authorQty;");
            foreach ($authorsIds AS $authorId) {
                $authors[] = $manager->getRepository(Author::class)->find((int)$authorId);
            }
            $book = new Book(
                join(' ', $faker->words),
                mb_substr(join(' ', $faker->words(300)), 0, 1024),
                random_int(0, 20) ? new DateTimeImmutable($faker->date()) : null,
                ...$authors
            );
            $manager->persist($book);

            if ($i % 100 === 0) {
                $manager->flush();
                $manager->clear(Book::class);
                $manager->clear(Author::class);
                gc_collect_cycles();
            }
        }
        $manager->flush();

    }

    public function getOrder()
    {
        return 2;
    }
}