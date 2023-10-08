<?php

namespace App\Dto\Input;

use App\Constraints\AuthorIdConstraint;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;


final class BookDto
{
    #[Assert\Length(min: 2, max: 255)]
    public readonly string $name;

    #[Assert\Length(max: 1024)]
    public readonly ?string $description;

    #[Assert\Date]
    #[Assert\When(expression: 'this.publicationDate !== ""', constraints: [
        new Assert\LessThanOrEqual(new DateTimeImmutable('today')),
    ])]
    public readonly ?string $publicationDate;
    #[Assert\NotBlank()]
    #[Assert\All([
        new AuthorIdConstraint()
    ])]
    public readonly array $authors;

    public function __construct(string $name, array $authors, ?string $description, ?string $publicationDate)
    {
        $this->name = trim($name);
        $this->description = trim($description);
        $this->publicationDate = $publicationDate;
        $this->authors = array_unique($authors);
    }
}