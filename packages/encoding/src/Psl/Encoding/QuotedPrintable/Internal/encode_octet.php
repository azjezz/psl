<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable\Internal;

use function dechex;
use function str_pad;
use function strtoupper;

use const STR_PAD_LEFT;

/**
 * @param int<0, 255> $byte
 *
 * @internal
 */
function encode_octet(int $byte): string
{
    /** @var non-negative-int $byte */
    return '=' . strtoupper(str_pad(dechex($byte), 2, '0', STR_PAD_LEFT));
}
