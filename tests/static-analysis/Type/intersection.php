<?php

declare(strict_types=1);

use Psl\Collection\Map;
use Psl\Collection\Vector;
use Psl\Result\ResultInterface;
use Psl\Type;

/**
 * @param Map&ResultInterface&stdClass&Vector $_value
 */
function takes_valid_intersection($_value): void
{
}

function test(): void
{
    /** @psalm-suppress MissingThrowsDocblock */
    $old_school_codec = Type\intersection(
        Type\instance_of(Map::class),
        Type\intersection(
            Type\instance_of(ResultInterface::class),
            Type\intersection(Type\instance_of(stdClass::class), Type\instance_of(Vector::class)),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $new_codec = Type\intersection(
        Type\instance_of(Map::class),
        Type\instance_of(ResultInterface::class),
        Type\instance_of(stdClass::class),
        Type\instance_of(Vector::class),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_intersection($old_school_codec->assert('any'));

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_intersection($new_codec->assert('any'));
}
