<?php

declare(strict_types=1);

namespace Psl\Type\Tests\StaticAnalysis;

use Psl\Collection\Map;
use Psl\Collection\Vector;
use Psl\Result\ResultInterface;
use Psl\Type;
use stdClass;

/**
 * @param Map&ResultInterface&stdClass&Vector $_
 */
function takes_valid_intersection(Map&ResultInterface&stdClass&Vector $_): void {}

function test(): void
{
    /** @psalm-suppress MissingThrowsDocblock */
    $oldSchoolCodec = Type\intersection(
        Type\instance_of(Map::class),
        Type\intersection(
            Type\instance_of(ResultInterface::class),
            Type\intersection(Type\instance_of(stdClass::class), Type\instance_of(Vector::class)),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $newCodec = Type\intersection(
        Type\instance_of(Map::class),
        Type\instance_of(ResultInterface::class),
        Type\instance_of(stdClass::class),
        Type\instance_of(Vector::class),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_intersection($oldSchoolCodec->assert('any'));

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_intersection($newCodec->assert('any'));
}
