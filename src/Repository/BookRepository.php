<?php

namespace App\Repository;

use App\Dto\Input\BooksFiltersDto;
use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findBooks(BooksFiltersDto $filters): array
    {
        $qb = $this->filterBooksQueryBuilder($filters);
        $qb->setFirstResult(($filters->page - 1) * $filters->limit)
            ->setMaxResults($filters->limit);
        $qb->orderBy('b.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function countBooks(BooksFiltersDto $filters): int
    {
        $qb = $this->filterBooksQueryBuilder($filters);
        $qb->select('COUNT(b.id)');

        return $qb->getQuery()->getSingleScalarResult();
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

    private function filterBooksQueryBuilder(BooksFiltersDto $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');
        if ($filters->name) {
            $qb->andWhere($qb->expr()->like('LOWER(b.name)', ':name'));
            $qb->setParameter('name', "$filters->name%");
        }
        if ($filters->description) {
            $qb->andWhere($qb->expr()->like('LOWER(b.description)', ':description'));
            $qb->setParameter('description', "$filters->description%");
        }
        if ($filters->minPublicationDate) {
            $qb->andWhere($qb->expr()->gte('b.publicationDate', ':minPublicationDate'));
            $qb->setParameter('minPublicationDate', "$filters->minPublicationDate");
        }
        if ($filters->maxPublicationDate) {
            $qb->andWhere($qb->expr()->lte('b.publicationDate', ':maxPublicationDate'));
            $qb->setParameter('maxPublicationDate', "$filters->maxPublicationDate");
        }

        return $qb;
    }
}
