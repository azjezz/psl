<?php

declare(strict_types=1);

namespace Psl\Socks\Internal;

use Psl\IO;
use Psl\Socks\Exception;
use SensitiveParameter;

use function chr;
use function strlen;

/**
 * Perform SOCKS5 username/password authentication (RFC 1929).
 *
 * @param non-empty-string $username
 * @param non-empty-string $password
 *
 * @throws Exception\AuthenticationException If authentication fails.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function socks5_authenticate(
    IO\ReadHandleInterface&IO\WriteHandleInterface $stream,
    string $username,
    #[SensitiveParameter] string $password,
): void {
    $usernameLen = strlen($username);
    $passwordLen = strlen($password);

    if ($usernameLen > 255) {
        throw new Exception\AuthenticationException('SOCKS5 username exceeds maximum length of 255 bytes.');
    }

    if ($passwordLen > 255) {
        throw new Exception\AuthenticationException('SOCKS5 password exceeds maximum length of 255 bytes.');
    }

    // Sub-negotiation version 1
    $auth = "\x01" . chr($usernameLen) . $username . chr($passwordLen) . $password;
    $stream->write($auth);

    $response = $stream->readFixedSize(2);
    if ($response[1] !== "\x00") {
        throw new Exception\AuthenticationException('SOCKS5 authentication failed: invalid credentials.');
    }
}
