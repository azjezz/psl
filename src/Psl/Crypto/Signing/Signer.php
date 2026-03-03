<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_sign_detached;

/**
 * Computes Ed25519 detached signatures using a secret key.
 */
final readonly class Signer implements SignerInterface
{
    public function __construct(
        #[SensitiveParameter]
        private SecretKey $secretKey,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function sign(#[SensitiveParameter] string $message): Signature
    {
        /** @var non-empty-string $raw */
        $raw = Internal\call_sodium(fn() => sodium_crypto_sign_detached($message, $this->secretKey->bytes));

        return new Signature($raw);
    }
}
