<?php

namespace App\GraphQL\Resolver;

use App\GraphQL\Mutator\AuthorMutator;
use App\Interfaces\Repository\AuthorRepositoryInterface;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class RootResolverMap extends ResolverMap
{
    private AuthorRepositoryInterface $authors;
    private AuthorMutator $authorMutator;

    public function __construct(
        AuthorRepositoryInterface $authors,
        AuthorMutator $authorMutator
    )
    {
        $this->authors = $authors;
        $this->authorMutator = $authorMutator;
    }

    protected function map()
    {
        return [
            'RootQuery' => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'author' => $this->authors->findById((int)$args['id']),
                        'authors' => $this->authors->findAllAuthors(),
                        default => null
                    };
                },
            ],
            'RootMutation' => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'createAuthor' => $this->authorMutator->create($args),
                        'editAuthor' => $this->authorMutator->edit($args),
                        default => null
                    };
                },
            ],
        ];
    }
}