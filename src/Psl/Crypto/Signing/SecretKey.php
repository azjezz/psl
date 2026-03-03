<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Exception;
use Psl\Str\Byte;
use SensitiveParameter;

final readonly class SecretKey
{
    /**
     * @param non-empty-string $bytes
     *
     * @throws Exception\InvalidArgumentException If the key is not exactly {@see SECRET_KEY_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (Byte\length($bytes) !== namespace\SECRET_KEY_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Signing secret key must be exactly ' . namespace\SECRET_KEY_BYTES . ' bytes.',
            );
        }
    }
}
