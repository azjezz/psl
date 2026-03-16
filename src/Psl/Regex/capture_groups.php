<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Type;

use function array_unique;
use function array_values;

/**
 * @param list<array-key> $groups
 *
 * @return Type\TypeInterface<array<array-key, string>>
 */
function capture_groups(array $groups): Type\TypeInterface
{
    $keys = array_values(array_unique([0, ...$groups]));
    $shape = [];
    foreach ($keys as $key) {
        $shape[$key] = Type\string();
    }

    return Type\shape($shape);
}
