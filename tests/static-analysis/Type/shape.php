<?php

declare(strict_types=1);

use Psl\Type;

function shoud_not_triger_redundant_condition(array $data): array
{
    /** @psalm-suppress MissingThrowsDocblock */
    $data2 = Type\shape([
        'meta' => Type\dict(Type\array_key(), Type\mixed()),
    ], true)->assert($data);

    $data2['meta']['id'] = 42;

    return $data2;
}
