<?php

namespace App\Constraints;

use App\Interfaces\Repository\AuthorRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AuthorIdConstraintValidator extends ConstraintValidator
{
    private AuthorRepositoryInterface $authors;

    public function __construct(AuthorRepositoryInterface $authors)
    {
        $this->authors = $authors;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $value = (int)$value;
        $author = is_int($value) ? $this->authors->findById($value) : null;
        if ($author === null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}