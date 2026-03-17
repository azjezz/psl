<?php

declare(strict_types=1);

namespace Psl\Crypto\StreamCipher;

enum Algorithm
{
    case Aes256Ctr;
    case Aes128Ctr;
    case XChaCha20;
}
