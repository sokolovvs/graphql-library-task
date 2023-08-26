<?php

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class AuthorDto
{
    #[Assert\Length(min: 2, max: 128)]
    public readonly string $name;

    public function __construct(string $name)
    {
        $this->name = trim($name);
    }
}