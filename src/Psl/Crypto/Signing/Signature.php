<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Exception;
use SensitiveParameter;

use function strlen;

/**
 * Represents a detached Ed25519 signature.
 */
final readonly class Signature
{
    /**
     * @param non-empty-string $bytes
     *
     * @throws Exception\InvalidArgumentException If the signature is not exactly {@see SIGNATURE_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (strlen($bytes) !== namespace\SIGNATURE_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Signature must be exactly ' . namespace\SIGNATURE_BYTES . ' bytes.',
            );
        }
    }
}
