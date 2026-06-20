<?php

declare(strict_types=1);

namespace Psl\Type\Tests\StaticAnalysis;

use Psl\Type;

/**
 * @param 'PENDING'|'PROCESSING'|'COMPLETED'|'ERROR' $_
 */
function takes_valid_state(string $_): void {}

function test(): void
{
    /** @psalm-suppress MissingThrowsDocblock */
    $oldSchoolCodec = Type\union::<string>(
        Type\literal_scalar::<string>('PENDING'),
        Type\union::<string>(
            Type\literal_scalar::<string>('PROCESSING'),
            Type\union::<string>(Type\literal_scalar::<string>('COMPLETED'), Type\literal_scalar::<string>('ERROR')),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $newCodec = Type\union::<string>(
        Type\literal_scalar::<string>('PENDING'),
        Type\literal_scalar::<string>('PROCESSING'),
        Type\literal_scalar::<string>('COMPLETED'),
        Type\literal_scalar::<string>('ERROR'),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    namespace\takes_valid_state($oldSchoolCodec->assert('any'));

    /** @psalm-suppress MissingThrowsDocblock */
    namespace\takes_valid_state($newCodec->assert('any'));
}
