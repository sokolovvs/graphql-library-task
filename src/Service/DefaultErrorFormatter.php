<?php

namespace App\Service;

use App\Interfaces\Service\ErrorFormatterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DefaultErrorFormatter implements ErrorFormatterInterface
{
    public function format(ConstraintViolationList $violationList): string
    {
        $errors = '';

        foreach (iterator_to_array($violationList) as $violation) {
            /* @var ConstraintViolation $violation */
            $errors .= $violation->getMessage() . PHP_EOL;
        }

        return $errors;
    }
}