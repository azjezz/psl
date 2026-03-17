<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

use Psl\Crypto\Exception;
use SensitiveParameter;

use function strlen;

final readonly class Key
{
    /**
     * @throws Exception\InvalidArgumentException If the key is not exactly {@see KEY_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (strlen($bytes) !== namespace\KEY_BYTES) {
            throw new Exception\InvalidArgumentException('AEAD key must be exactly ' . namespace\KEY_BYTES . ' bytes.');
        }
    }
}
