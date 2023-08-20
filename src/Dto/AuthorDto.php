<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AuthorDto
{
    #[Assert\Length(min: 2, max: 128)]
    public readonly string $name;

    public function __construct(string $name)
    {
        $this->name = trim($name);
    }
}