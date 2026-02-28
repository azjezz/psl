<?php

declare(strict_types=1);

namespace Psl\Socks\Internal;

/**
 * Map a SOCKS5 reply code to a human-readable error message.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function reply_message(int $code): string
{
    return match ($code) {
        0x01 => 'SOCKS5 general server failure.',
        0x02 => 'SOCKS5 connection not allowed by ruleset.',
        0x03 => 'SOCKS5 network unreachable.',
        0x04 => 'SOCKS5 host unreachable.',
        0x05 => 'SOCKS5 connection refused.',
        0x06 => 'SOCKS5 TTL expired.',
        0x07 => 'SOCKS5 command not supported.',
        0x08 => 'SOCKS5 address type not supported.',
        default => "SOCKS5 unknown error (code: {$code}).",
    };
}
