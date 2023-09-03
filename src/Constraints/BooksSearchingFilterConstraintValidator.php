<?php

namespace App\Constraints;

use App\Dto\Input\BooksFiltersDto;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class BooksSearchingFilterConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, BooksFiltersDto::class, 'Invalid constraint usage: ' . self::class);
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