<?php

declare(strict_types=1);

namespace Psl\Crypto\StreamCipher;

use Psl\Crypto\Exception;
use SensitiveParameter;

use function strlen;

final readonly class Key
{
    /**
     * @throws Exception\InvalidArgumentException If the key is not {@see AES_128_KEY_BYTES} or {@see AES_256_KEY_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        $length = strlen($bytes);
        if ($length !== namespace\AES_128_KEY_BYTES && $length !== namespace\AES_256_KEY_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Stream cipher key must be '
                . namespace\AES_128_KEY_BYTES
                . ' or '
                . namespace\AES_256_KEY_BYTES
                . ' bytes.',
            );
        }
    }
}
