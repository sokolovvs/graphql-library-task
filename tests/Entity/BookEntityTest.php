<?php

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Book;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class BookEntityTest extends TestCase
{
    public function testNewFailedIfWithoutAuthor(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Book must have min 1 author');
        new Book('xxx', 'yyy', new DateTimeImmutable('today'));
    }

    public function testErrorIfTryToRemoveLastAuthor(): void
    {
        $book = new Book('xxx', 'yyy', new DateTimeImmutable('today'), $author = new Author('Togar'));
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Book must have min 1 author');
        $book->removeAuthor($author);
    }
}