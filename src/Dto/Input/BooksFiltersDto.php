<?php

namespace App\Dto\Input;

use App\Constraints\BooksSearchingFilterConstraint;
use Symfony\Component\Validator\Constraints as Assert;

#[BooksSearchingFilterConstraint]
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
    #[Assert\GreaterThanOrEqual(1)]
    public readonly int $page;
    #[Assert\GreaterThanOrEqual(5)]
    #[Assert\LessThanOrEqual(200)]
    public readonly int $limit;

    public function __construct(
        ?string $name,
        ?string $description,
        ?string $minPublicationDate,
        ?string $maxPublicationDate,
        int $page = 1,
        int $limit = 25
    )
    {
        $this->name = mb_strtolower(trim($name));
        $this->description = mb_strtolower(trim($description));
        $this->minPublicationDate = $minPublicationDate;
        $this->maxPublicationDate = $maxPublicationDate;
        $this->page = $page;
        $this->limit = $limit;
    }
}