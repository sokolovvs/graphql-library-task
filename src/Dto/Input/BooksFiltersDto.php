<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class BooksFiltersDto
{
    #[Assert\Length(max: 256)]
    public readonly ?string $name;
    #[Assert\Length(max: 1024)]
    public readonly ?string $description;
    #[Assert\Date]
    public readonly ?string $minPublicationDate;
    #[Assert\Date]
    public readonly ?string $maxPublicationDate;

    public function __construct(?string $name, ?string $description, ?string $minPublicationDate, ?string $maxPublicationDate)
    {
        $this->name = mb_strtolower(trim($name));
        $this->description = mb_strtolower(trim($description));
        $this->minPublicationDate = $minPublicationDate;
        $this->maxPublicationDate = $maxPublicationDate;
    }
}