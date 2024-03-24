<?php

declare(strict_types=1);

namespace Psl\TCP\TLS;

use Psl\Default\DefaultInterface;

/**
 * Defines the security levels for TLS connections, based on OpenSSL's security levels.
 *
 * These levels control the algorithms and key lengths used during the TLS handshake and
 * secure connection. Higher levels enforce stronger security constraints, impacting
 * compatibility with certain clients and servers.
 *
 * @see https://www.openssl.org/docs/manmaster/man3/SSL_CTX_set_security_level.html
 */
enum SecurityLevel: int implements DefaultInterface
{
    /**
     * Level 0: Everything is permitted.
     *
     * This level retains compatibility with all versions of OpenSSL, imposing no restrictions.
     */
    case Level0 = 0;

    /**
     * Level 1: Corresponds to a minimum of 80 bits of security.
     *
     * Excludes parameters offering below 80 bits of security, prohibiting RSA, DSA, and DH keys shorter than 1024 bits,
     * ECC keys shorter than 160 bits, and any use of MD5 for the MAC. SSLv3, TLS 1.0, TLS 1.1, and DTLS 1.0 are disabled.
     */
    case Level1 = 1;

    /**
     * Level 2: Set to 112 bits of security.
     *
     * Builds on level 1 by also prohibiting RSA, DSA, and DH keys shorter than 2048 bits, ECC keys shorter than 224 bits,
     * any cipher suite using RC4, and disables compression.
     */
    case Level2 = 2;

    /**
     * Level 3: Set to 128 bits of security.
     *
     * Prohibits RSA, DSA, and DH keys shorter than 3072 bits, ECC keys shorter than 256 bits, cipher suites without forward secrecy,
     * and disables session tickets.
     */
    case Level3 = 3;

    /**
     * Level 4: Set to 192 bits of security.
     *
     * Increases requirements to RSA, DSA, and DH keys shorter than 7680 bits, ECC keys shorter than 384 bits, and disallows SHA1 for the MAC.
     */
    case Level4 = 4;

    /**
     * Level 5: The highest predefined security level, set to 256 bits of security.
     *
     * Prohibits RSA, DSA, and DH keys shorter than 15360 bits, and ECC keys shorter than 512 bits.
     */
    case Level5 = 5;

    /**
     * Returns the default security level for TLS connections, set to Level 2.
     *
     * Level 2 is chosen to balance improved security constraints against broad compatibility, reflecting modern best practices.
     *
     * @return static The default security level (Level 2).
     *
     * @pure
     */
    public static function default(): static
    {
        return static::Level2;
    }
}
