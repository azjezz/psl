<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use Psl\Crypto\Exception;
use Psl\Str\Byte;
use SensitiveParameter;

final readonly class PublicKey
{
    /**
     * @throws Exception\InvalidArgumentException If the key is not exactly {@see PUBLIC_KEY_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (Byte\length($bytes) !== namespace\PUBLIC_KEY_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Key exchange public key must be exactly ' . namespace\PUBLIC_KEY_BYTES . ' bytes.',
            );
        }
    }
}
