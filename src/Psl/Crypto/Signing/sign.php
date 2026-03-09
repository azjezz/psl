<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Exception;
use SensitiveParameter;

/**
 * Sign a message with an Ed25519 secret key.
 *
 * @throws Exception\RuntimeException If signing fails.
 */
function sign(#[SensitiveParameter] string $message, #[SensitiveParameter] SecretKey $secret_key): Signature
{
    return new Signer($secret_key)->sign($message);
}
