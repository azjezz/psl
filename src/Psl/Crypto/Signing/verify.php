<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use SensitiveParameter;

/**
 * Verify an Ed25519 detached signature.
 */
function verify(
    Signature $signature,
    #[SensitiveParameter]
    string $message,
    #[SensitiveParameter]
    PublicKey $public_key,
): bool {
    return new Verifier($public_key)->verify($signature, $message);
}
