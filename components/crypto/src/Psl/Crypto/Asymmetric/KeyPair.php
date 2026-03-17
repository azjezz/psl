<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

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
