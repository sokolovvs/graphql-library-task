<?php

namespace App\Constraints;

use App\Dto\Input\BooksFiltersDto;
use LogicException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BooksSearchingFilterConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof BooksFiltersDto) {
            throw new LogicException('Invalid constraint usage');
        }
        $allFieldsAreEmpty = empty($value->description) && empty($value->minPublicationDate) && empty($value->maxPublicationDate) && empty($value->name);
        if ($allFieldsAreEmpty) {
            return;
        }

        $otherFieldsAreNotEmpty = !empty($value->description) || !empty($value->minPublicationDate) || !empty($value->maxPublicationDate);
        if (empty($value->name) && $otherFieldsAreNotEmpty) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}