<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use function quoted_printable_decode;

/**
 * Decode a quoted-printable encoded string.
 */
function decode(string $data): string
{
    return quoted_printable_decode($data);
}
