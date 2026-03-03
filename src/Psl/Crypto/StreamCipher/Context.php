<?php

declare(strict_types=1);

namespace Psl\Crypto\StreamCipher;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;
use SensitiveParameter;

use function openssl_encrypt;
use function sodium_crypto_stream_xchacha20_xor_ic;

use const OPENSSL_RAW_DATA;
use const OPENSSL_ZERO_PADDING;

/**
 * Stream cipher context that maintains a keystream buffer across calls.
 *
 * This solves the partial-block problem by buffering unused keystream bytes
 * so that subsequent calls continue correctly from where the previous one left off.
 */
final class Context
{
    private string $iv;
    private string $keystreamBuffer = '';
    private int $chachaCounter = 0;
    /** @var positive-int */
    private readonly int $blockSize;

    /**
     * @throws Exception\InvalidArgumentException If the key length does not match the algorithm requirements.
     * @throws Exception\RuntimeException If the IV length is invalid.
     */
    public function __construct(
        #[SensitiveParameter]
        private readonly Key $key,
        #[SensitiveParameter]
        string $iv,
        private readonly Algorithm $algorithm,
    ) {
        $this->iv = $iv;

        $this->blockSize = match ($algorithm) {
            Algorithm::Aes256Ctr, Algorithm::Aes128Ctr => namespace\AES_CTR_BLOCK_BYTES,
            Algorithm::XChaCha20 => namespace\XCHACHA20_BLOCK_BYTES,
        };

        $expectedKeySize = match ($algorithm) {
            Algorithm::Aes256Ctr => namespace\AES_256_KEY_BYTES,
            Algorithm::Aes128Ctr => namespace\AES_128_KEY_BYTES,
            Algorithm::XChaCha20 => namespace\XCHACHA20_KEY_BYTES,
        };

        if (Byte\length($key->bytes) !== $expectedKeySize) {
            throw new Exception\InvalidArgumentException('Key size does not match algorithm requirements.');
        }

        $expectedIvSize = match ($algorithm) {
            Algorithm::Aes256Ctr, Algorithm::Aes128Ctr => namespace\AES_CTR_IV_BYTES,
            Algorithm::XChaCha20 => namespace\XCHACHA20_IV_BYTES,
        };

        if (Byte\length($iv) !== $expectedIvSize) {
            throw new Exception\RuntimeException('IV size does not match algorithm requirements.');
        }
    }

    /**
     * XOR data with the keystream, maintaining buffer continuity.
     *
     * @throws Exception\RuntimeException If keystream generation fails.
     */
    public function apply(#[SensitiveParameter] string $data): string
    {
        $needed = Byte\length($data);
        $result = '';
        $offset = 0;

        while ($needed > 0) {
            if ($this->keystreamBuffer === '') {
                $this->keystreamBuffer = $this->generateKeystreamBlock();
            }

            $available = Byte\length($this->keystreamBuffer);
            $use = Math\minva($needed, $available);

            $dataChunk = Byte\slice($data, $offset, $use);
            $keyChunk = Byte\slice($this->keystreamBuffer, 0, $use);

            /**
             * @mago-expect analysis:invalid-operand,invalid-operand - mago does not like string ^ string
             * @var string $xored
             */
            $xored = $dataChunk ^ $keyChunk;
            $result .= $xored;

            $this->keystreamBuffer = Byte\slice($this->keystreamBuffer, $use);
            $offset += $use;
            $needed -= $use;
        }

        return $result;
    }

    /**
     * @throws Exception\RuntimeException If keystream generation fails.
     */
    private function generateKeystreamBlock(): string
    {
        return match ($this->algorithm) {
            Algorithm::Aes256Ctr => $this->generateAesCtrBlock('aes-256-ctr'),
            Algorithm::Aes128Ctr => $this->generateAesCtrBlock('aes-128-ctr'),
            Algorithm::XChaCha20 => $this->generateXChaCha20Block(),
        };
    }

    /**
     * @throws Exception\RuntimeException If AES-CTR keystream generation fails.
     */
    private function generateAesCtrBlock(string $cipher): string
    {
        $zeros = Str\repeat("\x00", $this->blockSize);
        $keystream = openssl_encrypt(
            $zeros,
            $cipher,
            $this->key->bytes,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->iv,
        );

        if ($keystream === false) {
            throw new Exception\RuntimeException('AES-CTR keystream generation failed.');
        }

        $this->advanceAesCtrIv();

        return $keystream;
    }

    /**
     * Increment the 128-bit IV (big-endian counter) for AES-CTR.
     */
    private function advanceAesCtrIv(): void
    {
        for ($i = namespace\AES_CTR_IV_BYTES - 1; $i >= 0; $i--) {
            $val = Byte\ord($this->iv[$i]) + 1;
            $this->iv[$i] = Byte\chr($val & 0xff);
            if ($val < 256) {
                break;
            }
        }
    }

    /**
     * @throws Exception\RuntimeException If XChaCha20 keystream generation fails.
     */
    private function generateXChaCha20Block(): string
    {
        $zeros = Str\repeat("\x00", $this->blockSize);
        $keystream = Internal\call_sodium(fn() => sodium_crypto_stream_xchacha20_xor_ic(
            $zeros,
            $this->iv,
            $this->chachaCounter,
            $this->key->bytes,
        ));

        $this->chachaCounter++;

        return $keystream;
    }
}
