<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_box_seal;

/**
 * Seal a message for a recipient's public key (anonymous sender).
 *
 * Only the holder of the corresponding secret key can open the sealed message.
 */
function seal(#[SensitiveParameter] string $plaintext, #[SensitiveParameter] PublicKey $publicKey): string
{
    return Internal\call_sodium(fn() => sodium_crypto_box_seal($plaintext, $publicKey->bytes));
}
