<?php

namespace App\Repository;

use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository implements BookRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findById(int $id): ?Book
    {
        return $this->find($id);
    }

    public function findAllBooks(): array
    {
        return $this->findAll();
    }

    public function save(Book $book): void
    {
        $this->_em->persist($book);
        $this->_em->flush();
    }

    public function remove(Book $book): void
    {
        $this->_em->remove($book);
        $this->_em->flush();
    }
}
