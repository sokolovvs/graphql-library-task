<?php

namespace App\Repository;

use App\Entity\Author;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository implements AuthorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function findById(int $id): ?Author
    {
        return $this->find($id);
    }

    public function findAllAuthors(): array
    {
        return $this->findAll();
    }

    public function save(Author $author): void
    {
        $this->_em->persist($author);
        $this->_em->flush();
    }

    public function remove(Author $author): void
    {
        $this->_em->remove($author);
        $this->_em->flush();
    }
}
