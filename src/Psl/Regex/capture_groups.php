<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Dict;
use Psl\Type;

/**
 * @param list<array-key> $groups
 *
 * @return Type\TypeInterface<array<array-key, string>>
 *
 * @psalm-suppress MixedReturnTypeCoercion - Psalm loses track of the keys. No worries, another psalm plugin fixes this!
 */
function capture_groups(array $groups): Type\TypeInterface
{
    return Type\shape(
        Dict\from_keys(
            Dict\unique([0, ...$groups]),
            /**
             * @return Type\TypeInterface<string>
             */
            static fn(): Type\TypeInterface => Type\string()
        )
    );
}
