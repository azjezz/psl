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
    $oldSchoolCodec = Type\intersection::<Map, ResultInterface&stdClass&Vector>(
        Type\instance_of::<Map>(Map::class),
        Type\intersection::<ResultInterface, stdClass&Vector>(
            Type\instance_of::<ResultInterface>(ResultInterface::class),
            Type\intersection::<stdClass, Vector>(Type\instance_of::<stdClass>(stdClass::class), Type\instance_of::<Vector>(Vector::class)),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    namespace\takes_valid_intersection($oldSchoolCodec->assert('any'));
}
