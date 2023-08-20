<?php

namespace App\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class AuthorIdConstraint extends Constraint
{
    public string $message = 'Invalid authorId';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}