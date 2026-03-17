<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use Psl\TLS\Version;

use const STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_0_SERVER;
use const STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_1_SERVER;
use const STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_2_SERVER;
use const STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_3_SERVER;

/**
 * Compute the crypto method bitmask from min/max TLS versions.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function crypto_method(null|Version $minimum, null|Version $maximum, bool $server): int
{
    $minimum ??= Version::Tls10;
    $maximum ??= Version::Tls13;

    $methods = $server
        ? [
            Version::Tls10->value => STREAM_CRYPTO_METHOD_TLSv1_0_SERVER,
            Version::Tls11->value => STREAM_CRYPTO_METHOD_TLSv1_1_SERVER,
            Version::Tls12->value => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER,
            Version::Tls13->value => STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
        ]
        : [
            Version::Tls10->value => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT,
            Version::Tls11->value => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT,
            Version::Tls12->value => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            Version::Tls13->value => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
        ];

    $result = 0;
    foreach ($methods as $version => $method) {
        if (!($version >= $minimum->value && $version <= $maximum->value)) {
            continue;
        }

        $result |= $method;
    }

    return $result;
}
