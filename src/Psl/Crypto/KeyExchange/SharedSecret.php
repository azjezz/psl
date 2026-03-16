<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use Psl\Crypto\Exception;
use SensitiveParameter;

use function strlen;

final readonly class SharedSecret
{
    /**
     * @param non-empty-string $bytes
     *
     * @throws Exception\InvalidArgumentException If the shared secret is not exactly {@see SHARED_SECRET_BYTES} bytes.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $bytes,
    ) {
        if (strlen($bytes) !== namespace\SHARED_SECRET_BYTES) {
            throw new Exception\InvalidArgumentException(
                'Shared secret must be exactly ' . namespace\SHARED_SECRET_BYTES . ' bytes.',
            );
        }
    }
}
