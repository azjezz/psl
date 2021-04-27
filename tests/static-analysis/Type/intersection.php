<?php

declare(strict_types=1);

use Psl\Collection\Map;
use Psl\Result\ResultInterface;
use Psl\Type;

/**
 * @psalm-suppress UnusedParam
 *
 * @param Map&ResultInterface&stdClass $value
 */
function takes_valid_intersection($value): void
{
}

function test(): void
{
    /** @psalm-suppress MissingThrowsDocblock */
    $old_school_codec = Type\intersection(
        Type\object(Map::class),
        Type\intersection(
            Type\object(ResultInterface::class),
            Type\object(stdClass::class),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $new_codec = Type\intersection(
        Type\object(Map::class),
        Type\object(ResultInterface::class),
        Type\object(stdClass::class),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_intersection($old_school_codec->assert('any'));

    /**
     * @psalm-suppress MissingThrowsDocblock
     * @psalm-suppress InvalidArgument
     */
    takes_valid_intersection($new_codec->assert('any'));
}
