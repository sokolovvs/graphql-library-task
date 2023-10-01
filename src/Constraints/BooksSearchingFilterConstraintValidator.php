<?php

namespace App\Constraints;

use App\Dto\Input\BooksFiltersDto;
use Overblog\GraphQLBundle\Validator\ValidationNode;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class BooksSearchingFilterConstraintValidator extends ConstraintValidator
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if($value instanceof ValidationNode) {
            $args = (array)$value->getResolverArg('args');
                $filters = array_shift($args)['filters'] ?? [];
            $value = $this->serializer->deserialize(json_encode($filters), BooksFiltersDto::class, 'json');
        }

        Assert::isInstanceOf($value, BooksFiltersDto::class, 'Invalid constraint usage: ' . self::class);
        $otherFieldsAreNotEmpty = !empty($value->description) || !empty($value->minPublicationDate) || !empty($value->maxPublicationDate);
        if (empty($value->name) && $otherFieldsAreNotEmpty) {
            $this->context->buildViolation($constraint->message)
                ->atPath('name')
                ->addViolation();
        }
    }
}