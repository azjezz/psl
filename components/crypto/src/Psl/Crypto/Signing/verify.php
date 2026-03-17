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
    PublicKey $publicKey,
): bool {
    return new Verifier($publicKey)->verify($signature, $message);
}
