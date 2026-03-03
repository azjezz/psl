<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Exception;
use SensitiveParameter;

interface SignerInterface
{
    /**
     * Computes a detached Ed25519 signature for the given message.
     *
     * A detached signature does not include the original message contents.
     *
     * The verifier will need both this signature and the exact original message
     * to successfully verify authenticity.
     *
     * @param string $message The plaintext message to sign.
     *
     * @return Signature The resulting 64-byte Ed25519 signature.
     *
     * @throws Exception\RuntimeException If the cryptographic signing operation fails.
     */
    public function sign(#[SensitiveParameter] string $message): Signature;
}
