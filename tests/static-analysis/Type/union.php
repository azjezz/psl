<?php

declare(strict_types=1);

use Psl\Type;

/**
 * @param 'PENDING'|'PROCESSING'|'COMPLETED'|'ERROR' $state
 */
function takes_valid_state(string $_state): void
{
}

function test(): void
{
    /** @psalm-suppress MissingThrowsDocblock */
    $old_school_codec = Type\union(
        Type\literal_scalar('PENDING'),
        Type\union(
            Type\literal_scalar('PROCESSING'),
            Type\union(Type\literal_scalar('COMPLETED'), Type\literal_scalar('ERROR')),
        ),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $new_codec = Type\union(
        Type\literal_scalar('PENDING'),
        Type\literal_scalar('PROCESSING'),
        Type\literal_scalar('COMPLETED'),
        Type\literal_scalar('ERROR'),
    );

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_state($old_school_codec->assert('any'));

    /** @psalm-suppress MissingThrowsDocblock */
    takes_valid_state($new_codec->assert('any'));
}
