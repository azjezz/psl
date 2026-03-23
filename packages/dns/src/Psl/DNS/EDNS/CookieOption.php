<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

use Psl\DNS\Exception\RuntimeException;
use Random\RandomException;

use function random_bytes;

/**
 * DNS COOKIE option (RFC 7873, option code 10).
 *
 * Provides lightweight transaction authentication to protect against
 * off-path spoofing and amplification attacks.
 *
 * @api
 */
final class CookieOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 10;
    }

    /**
     * Length of the client cookie in bytes.
     */
    private const int CLIENT_COOKIE_LENGTH = 8;

    /**
     * @param non-empty-string $clientCookie 8-byte client cookie (raw bytes).
     * @param string $serverCookie 8-32 byte server cookie (raw bytes); empty in initial queries.
     */
    public function __construct(
        public readonly string $clientCookie,
        public readonly string $serverCookie = '',
    ) {}

    /**
     * Create a CookieOption with a random client cookie for initial queries.
     *
     * @throws RuntimeException If secure random generation fails.
     */
    public static function random(): self
    {
        try {
            $clientCookie = random_bytes(self::CLIENT_COOKIE_LENGTH);

            return new self($clientCookie);
        } catch (RandomException $e) {
            throw RuntimeException::forCookieGenerationFailure($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        return $this->clientCookie . $this->serverCookie;
    }
}
