<?php

namespace App\Interfaces\Service;

use Symfony\Component\Validator\ConstraintViolationList;

interface ErrorFormatterInterface
{
    public function format(ConstraintViolationList $violationList): string;
}