<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use SensitiveParameter;

interface VerifierInterface
{
    /**
     * Verifies a detached Ed25519 signature against a given message.
     *
     * @param Signature $signature The detached signature to verify.
     * @param string    $message   The original plaintext message that was signed.
     *
     * @return bool True if the signature is perfectly valid for the given message and public key, false otherwise.
     */
    public function verify(Signature $signature, #[SensitiveParameter] string $message): bool;
}
