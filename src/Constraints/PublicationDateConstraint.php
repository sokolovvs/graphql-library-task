<?php

namespace App\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class PublicationDateConstraint extends Constraint
{
    public string $message = 'Invalid publication date';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}