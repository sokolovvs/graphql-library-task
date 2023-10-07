<?php

namespace App\Constraints;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PublicationDateConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (false === DateTimeImmutable::createFromFormat('Y-m-d', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }

        if (DateTimeImmutable::createFromFormat('Y-m-d', $value)->setTime(0, 0) >= new DateTimeImmutable('today')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}