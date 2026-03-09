<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use SensitiveParameter;

final readonly class KeyPair
{
    public function __construct(
        #[SensitiveParameter]
        public PublicKey $publicKey,
        #[SensitiveParameter]
        public SecretKey $secretKey,
    ) {}
}
