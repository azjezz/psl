<?php

declare(strict_types=1);

namespace Psl\Socks\Internal;

use Psl\IO;
use Psl\Socks\Exception;

use function chr;
use function inet_pton;
use function ord;
use function pack;
use function strlen;

/**
 * Perform a SOCKS5 handshake on an existing stream.
 *
 * Implements RFC 1928 (SOCKS5) and RFC 1929 (username/password auth).
 *
 * @param non-empty-string $host Target host to connect to through the proxy.
 * @param int<0, 65535> $port Target port to connect to through the proxy.
 * @param non-empty-string|null $username Optional authentication username.
 * @param non-empty-string|null $password Optional authentication password.
 *
 * @throws Exception\SocksException If the SOCKS5 handshake fails.
 * @throws Exception\AuthenticationException If authentication fails.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function socks5_handshake(
    IO\ReadHandleInterface&IO\WriteHandleInterface $stream,
    string $host,
    int $port,
    null|string $username,
    #[\SensitiveParameter] null|string $password,
): void {
    if ($username !== null && $password !== null) {
        // Offer no-auth (0x00) and username/password (0x02)
        $stream->write("\x05\x02\x00\x02");
    } else {
        // Offer only no-auth (0x00)
        $stream->write("\x05\x01\x00");
    }

    $response = $stream->readFixedSize(2);
    if ($response[0] !== "\x05") {
        throw new Exception\SocksException('Invalid SOCKS version in server response.');
    }

    $method = ord($response[1]);
    if ($method === 0xFF) {
        throw new Exception\SocksException('SOCKS5 server rejected all offered authentication methods.');
    }

    if ($method === 0x02) {
        if ($username === null || $password === null) {
            throw new Exception\AuthenticationException(
                'SOCKS5 server requires authentication but no credentials were provided.',
            );
        }

        socks5_authenticate($stream, $username, $password);
    } elseif ($method !== 0x00) {
        throw new Exception\SocksException("SOCKS5 server selected unsupported authentication method: {$method}.");
    }

    $request = "\x05\x01\x00"; // Version 5, CONNECT command, reserved
    $packed = inet_pton($host);
    if ($packed !== false && strlen($packed) === 4) {
        // IPv4 address
        $request .= "\x01" . $packed;
    } elseif ($packed !== false && strlen($packed) === 16) {
        // IPv6 address
        $request .= "\x04" . $packed;
    } else {
        // Domain name
        $hostLen = strlen($host);
        if ($hostLen > 255) {
            throw new Exception\SocksException('Domain name exceeds maximum length of 255 bytes.');
        }

        $request .= "\x03" . chr($hostLen) . $host;
    }

    $request .= pack('n', $port);
    $stream->write($request);

    $response = $stream->readFixedSize(4);
    if ($response[0] !== "\x05") {
        throw new Exception\SocksException('Invalid SOCKS version in connection response.');
    }

    $reply = ord($response[1]);
    if ($reply !== 0x00) {
        throw new Exception\SocksException(reply_message($reply));
    }

    $addressType = ord($response[3]);
    match ($addressType) {
        0x01 => $stream->readFixedSize(4), // IPv4: 4 bytes
        0x04 => $stream->readFixedSize(16), // IPv6: 16 bytes
        0x03 => (static function () use ($stream): string {
            $len = ord($stream->readFixedSize(1));
            if ($len === 0) {
                throw new Exception\SocksException('SOCKS5 server returned empty domain in bound address.');
            }

            return $stream->readFixedSize($len);
        })(),
        default => throw new Exception\SocksException("Unknown address type in SOCKS5 response: {$addressType}."),
    };

    // Read and discard the bound port (2 bytes)
    $stream->readFixedSize(2);
}
