<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_sign_verify_detached;

/**
 * Verifies Ed25519 detached signatures using a public key.
 */
final readonly class Verifier implements VerifierInterface
{
    public function __construct(
        #[SensitiveParameter]
        private PublicKey $publicKey,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function verify(Signature $signature, #[SensitiveParameter] string $message): bool
    {
        return Internal\call_sodium(fn() => sodium_crypto_sign_verify_detached(
            $signature->bytes,
            $message,
            $this->publicKey->bytes,
        ));
    }
}
