<?php

namespace App\EventListener;

use App\Entity\Author;
use App\Entity\Book;
use App\Interfaces\Repository\BookRepositoryInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

class AuthorBooksEventListener
{
    private BookRepositoryInterface $books;

    public function __construct(BookRepositoryInterface $books)
    {
        $this->books = $books;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();
        $booksIds = array_unique(
            array_merge(
                $this->collectBooksIds($uow->getScheduledEntityInsertions()),
                $this->collectBooksIds($uow->getScheduledEntityUpdates()),
                $this->collectBooksIds($uow->getScheduledEntityDeletions()),
            )
        );

        foreach ($uow->getScheduledEntityUpdates() as $author) {
            if ($author instanceof Author) {
                $author->updateBookNumber();
                $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(Author::class), $author);
            }
        }
        foreach ($booksIds as $id) {
            $book = $this->books->findById($id);
            foreach ($book->getAuthors() as $author) {
                $author->updateBookNumber();
                $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(Author::class), $author);
            }
        }

    }

    private function collectBooksIds(array $entities): array
    {
        $booksIds = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof Book) {
                continue;
            }
            $booksIds[] = $entity->getId();
        }

        return $booksIds;
    }
}