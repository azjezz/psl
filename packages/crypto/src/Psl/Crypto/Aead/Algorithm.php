<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

enum Algorithm
{
    case Aes256Gcm;
    case XChaCha20Poly1305;
    case ChaCha20Poly1305;
}
