<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Provider\en_US\Person;

final class AuthorsFixture extends Fixture implements OrderedFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $faker = new Generator();
        $faker->addProvider(new Person($faker));
        for ($i = 0; $i < 100; $i++) {
            $author = new Author($faker->name);
            $manager->persist($author);

            if ($i % 100 === 0) {
                $manager->flush();
                $manager->clear(Author::class);
                gc_collect_cycles();
            }
        }
        $manager->flush();

    }

    public function getOrder()
    {
        return 1;
    }
}