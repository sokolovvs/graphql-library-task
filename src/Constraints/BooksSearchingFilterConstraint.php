<?php

namespace App\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class BooksSearchingFilterConstraint extends Constraint
{
    public string $message = 'Invalid searching criteria is invalid. You can\'t search by other fields without name.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}