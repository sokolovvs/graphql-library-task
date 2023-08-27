<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class AuthorsFiltersDto
{
    public readonly ?string $name;
    #[Assert\GreaterThanOrEqual(1)]
    public readonly int $page;
    #[Assert\GreaterThanOrEqual(5)]
    #[Assert\LessThanOrEqual(200)]
    public readonly int $limit;

    public function __construct(?string $name, int $page = 1, int $limit = 25)
    {
        $this->name = mb_strtolower(trim($name));
        $this->page = $page;
        $this->limit = $limit;
    }
}