<?php

namespace App\Repository;

use App\Dto\Input\AuthorsFiltersDto;
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

    public function findAuthors(AuthorsFiltersDto $filters): array
    {
        $qb = $this->createQueryBuilder('a');
        if ($filters->name) {
            $qb->andWhere($qb->expr()->like('LOWER(a.name)', ':name'));
            $qb->setParameter('name', "$filters->name%");
        }
        $qb->setFirstResult(($filters->page - 1) * $filters->limit)
            ->setMaxResults($filters->limit);

        $qb->orderBy('a.id', 'ASC');

        return $qb->getQuery()->getResult();
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
