<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Exception;
use SensitiveParameter;

use function strlen;

final readonly class SecretKey
{
    /**
     * @param non-empty-string $bytes The secret key bytes. Must be exactly {@see SECRET_KEY_BYTES} bytes.
     *
     * @throws Exception\InvalidArgumentException If the key is not exactly {@see SECRET_KEY_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (strlen($bytes) !== namespace\SECRET_KEY_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Asymmetric encryption secret key must be exactly ' . namespace\SECRET_KEY_BYTES . ' bytes.',
            );
        }
    }
}
