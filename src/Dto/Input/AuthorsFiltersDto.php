<?php

namespace App\Dto\Input;

final class AuthorsFiltersDto
{
    public readonly ?string $name;

    public function __construct(?string $name)
    {
        $this->name = mb_strtolower(trim($name));
    }
}