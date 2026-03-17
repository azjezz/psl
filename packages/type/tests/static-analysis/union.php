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
    $oldSchoolCodec = Type\union(
        Type\literal_scalar('PENDING'),
        Type\union(
            Type\literal_scalar('PROCESSING'),
            Type\union(Type\literal_scalar('COMPLETED'), Type\literal_scalar('ERROR')),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $newCodec = Type\union(
        Type\literal_scalar('PENDING'),
        Type\literal_scalar('PROCESSING'),
        Type\literal_scalar('COMPLETED'),
        Type\literal_scalar('ERROR'),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_state($oldSchoolCodec->assert('any'));

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_state($newCodec->assert('any'));
}
