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
function sign(#[SensitiveParameter] string $message, #[SensitiveParameter] SecretKey $secretKey): Signature
{
    return new Signer($secretKey)->sign($message);
}
