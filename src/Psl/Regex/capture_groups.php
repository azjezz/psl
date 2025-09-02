<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Dict;
use Psl\Type;

/**
 * @param list<array-key> $groups
 *
 * @return Type\TypeInterface<array<array-key, string>>
 */
function capture_groups(array $groups): Type\TypeInterface
{
    return Type\shape(Dict\from_keys(
        Dict\unique([0, ...$groups]),
        /**
         * @return Type\TypeInterface<string>
         */
        Type\string(...),
    ));
}
