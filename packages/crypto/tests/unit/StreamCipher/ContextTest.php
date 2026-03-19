<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\StreamCipher;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\StreamCipher;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;

use function openssl_encrypt;
use function sodium_crypto_stream_xchacha20_xor;
use function str_repeat;

use const OPENSSL_RAW_DATA;

final class ContextTest extends TestCase
{
    public function testAes256CtrRoundtrip(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        $plaintext = 'Hello, AES-256-CTR stream cipher!';
        $encrypted = $encCtx->apply($plaintext);
        $decrypted = $decCtx->apply($encrypted);

        static::assertSame($plaintext, $decrypted);
        static::assertNotSame($plaintext, $encrypted);
    }

    public function testAes128CtrRoundtrip(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(16));
        $iv = SecureRandom\bytes(16);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes128Ctr);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes128Ctr);

        $plaintext = 'Hello, AES-128-CTR!';
        $encrypted = $encCtx->apply($plaintext);
        $decrypted = $decCtx->apply($encrypted);

        static::assertSame($plaintext, $decrypted);
    }

    public function testXChaCha20Roundtrip(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(24);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);

        $plaintext = 'Hello, XChaCha20 stream cipher!';
        $encrypted = $encCtx->apply($plaintext);
        $decrypted = $decCtx->apply($encrypted);

        static::assertSame($plaintext, $decrypted);
    }

    public function testPartialBlockContinuity(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        $enc1 = $encCtx->apply('Hello');
        $enc2 = $encCtx->apply(', ');
        $enc3 = $encCtx->apply('World!');

        $decrypted = $decCtx->apply($enc1 . $enc2 . $enc3);

        static::assertSame('Hello, World!', $decrypted);
    }

    public function testBufferContinuityAcrossMultipleCalls(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $encSingle = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $singleEncrypted = $encSingle->apply('ABCDEFGHIJKLMNOP');

        $encMulti = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $multiEncrypted = '';
        $multiEncrypted .= $encMulti->apply('ABCD');
        $multiEncrypted .= $encMulti->apply('EFGHIJ');
        $multiEncrypted .= $encMulti->apply('KLMNOP');

        static::assertSame($singleEncrypted, $multiEncrypted);
    }

    public function testInvalidIvLengthForAesCtrThrows(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('IV size does not match algorithm requirements.');
        new StreamCipher\Context($key, SecureRandom\bytes(24), StreamCipher\Algorithm::Aes256Ctr);
    }

    public function testInvalidIvLengthForXChaCha20Throws(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('IV size does not match algorithm requirements.');
        new StreamCipher\Context($key, SecureRandom\bytes(16), StreamCipher\Algorithm::XChaCha20);
    }

    public function testInvalidKeyLengthForAlgorithmThrows(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(16));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key size does not match algorithm requirements.');
        new StreamCipher\Context($key, SecureRandom\bytes(16), StreamCipher\Algorithm::Aes256Ctr);
    }

    public function testAes128CtrRejects32ByteKey(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key size does not match algorithm requirements.');
        new StreamCipher\Context($key, SecureRandom\bytes(16), StreamCipher\Algorithm::Aes128Ctr);
    }

    public function testEmptyData(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        static::assertSame('', $ctx->apply(''));
    }

    public function testXChaCha20PartialBlocks(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(24);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);

        $enc1 = $encCtx->apply('Short');
        $enc2 = $encCtx->apply(' message');
        $enc3 = $encCtx->apply(' here!');

        $decrypted = $decCtx->apply($enc1 . $enc2 . $enc3);
        static::assertSame('Short message here!', $decrypted);
    }

    public function testXChaCha20BufferContinuity(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(24);

        $encSingle = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $singleEncrypted = $encSingle->apply('ABCDEFGHIJKLMNOPQRSTUVWXYZ012345');

        $encMulti = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $multiEncrypted = '';
        $multiEncrypted .= $encMulti->apply('ABCDEFGH');
        $multiEncrypted .= $encMulti->apply('IJKLMNOP');
        $multiEncrypted .= $encMulti->apply('QRSTUVWX');
        $multiEncrypted .= $encMulti->apply('YZ012345');

        static::assertSame($singleEncrypted, $multiEncrypted);
    }

    public function testLargeDataAcrossMultipleBlocks(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        $plaintext = Str\repeat('Z', 10_000);
        $encrypted = $encCtx->apply($plaintext);
        $decrypted = $decCtx->apply($encrypted);

        static::assertSame($plaintext, $decrypted);
    }

    public function testXChaCha20LargeData(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(24);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);

        $plaintext = Str\repeat('Q', 10_000);
        $encrypted = $encCtx->apply($plaintext);
        $decrypted = $decCtx->apply($encrypted);

        static::assertSame($plaintext, $decrypted);
    }

    public function testDifferentKeysProduceDifferentOutput(): void
    {
        $key1 = new StreamCipher\Key(SecureRandom\bytes(32));
        $key2 = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $ctx1 = new StreamCipher\Context($key1, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $ctx2 = new StreamCipher\Context($key2, $iv, StreamCipher\Algorithm::Aes256Ctr);

        static::assertNotSame($ctx1->apply('same data'), $ctx2->apply('same data'));
    }

    public function testDifferentIvsProduceDifferentOutput(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv1 = SecureRandom\bytes(16);
        $iv2 = SecureRandom\bytes(16);

        $ctx1 = new StreamCipher\Context($key, $iv1, StreamCipher\Algorithm::Aes256Ctr);
        $ctx2 = new StreamCipher\Context($key, $iv2, StreamCipher\Algorithm::Aes256Ctr);

        static::assertNotSame($ctx1->apply('same data'), $ctx2->apply('same data'));
    }

    public function testSingleByteAtATime(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(16);

        $plaintext = 'Hello, World!';
        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        $encrypted = '';
        for ($i = 0; $i < Byte\length($plaintext); $i++) {
            $encrypted .= $encCtx->apply($plaintext[$i]);
        }

        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        static::assertSame($plaintext, $decCtx->apply($encrypted));
    }

    public function testBinaryDataRoundtrip(): void
    {
        $key = new StreamCipher\Key(SecureRandom\bytes(32));
        $iv = SecureRandom\bytes(24);

        $encCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $decCtx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);

        $plaintext = SecureRandom\bytes(500);
        $encrypted = $encCtx->apply($plaintext);

        static::assertSame($plaintext, $decCtx->apply($encrypted));
    }

    public function testXChaCha20MultiBlockMatchesSodiumStream(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(24);

        $plaintext = Str\repeat('A', 256);

        $expected = sodium_crypto_stream_xchacha20_xor($plaintext, $iv, $keyBytes);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $actual = $ctx->apply($plaintext);

        static::assertSame($expected, $actual);
    }

    public function testXChaCha20ChunkedMatchesSodiumStream(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(24);

        $plaintext = Str\repeat('B', 200);

        $expected = sodium_crypto_stream_xchacha20_xor($plaintext, $iv, $keyBytes);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::XChaCha20);
        $actual = '';
        $actual .= $ctx->apply(Byte\slice($plaintext, 0, 30));
        $actual .= $ctx->apply(Byte\slice($plaintext, 30, 70));
        $actual .= $ctx->apply(Byte\slice($plaintext, 100, 100));

        static::assertSame($expected, $actual);
    }

    /**
     * Verify our AES-CTR output matches openssl_encrypt for multi-block data.
     *
     * This kills mutations in advanceAesCtrIv() because if the IV increment
     * is wrong, blocks after the first will produce wrong keystream.
     */
    public function testAes256CtrMatchesOpensslForMultipleBlocks(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(16);

        // 48 bytes = 3 AES blocks (16 bytes each)
        $plaintext = str_repeat('X', 48);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $ourOutput = $ctx->apply($plaintext);

        // OpenSSL handles IV increment internally in CTR mode
        $reference = openssl_encrypt($plaintext, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);

        static::assertSame($reference, $ourOutput);
    }

    public function testAes128CtrMatchesOpensslForMultipleBlocks(): void
    {
        $keyBytes = SecureRandom\bytes(16);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(16);

        // 64 bytes = 4 AES blocks
        $plaintext = str_repeat('Y', 64);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes128Ctr);
        $ourOutput = $ctx->apply($plaintext);

        $reference = openssl_encrypt($plaintext, 'aes-128-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);

        static::assertSame($reference, $ourOutput);
    }

    /**
     * Test with IV near overflow (0xff...ff) to exercise the carry logic
     * in advanceAesCtrIv's loop over all 16 bytes.
     */
    public function testAesCtrIvOverflowCarry(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);

        $iv = str_repeat("\x00", 14) . "\x00\xfe";

        $plaintext = str_repeat('Z', 48);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $ourOutput = $ctx->apply($plaintext);

        $reference = openssl_encrypt($plaintext, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);

        static::assertSame($reference, $ourOutput);
    }

    /**
     * Test with IV at all 0xff to exercise carry propagation across all bytes.
     */
    public function testAesCtrIvFullCarryPropagation(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);

        $iv = str_repeat("\xff", 16);

        $plaintext = str_repeat('W', 32);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $ourOutput = $ctx->apply($plaintext);

        $reference = openssl_encrypt($plaintext, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);

        static::assertSame($reference, $ourOutput);
    }

    /**
     * Test chunked encryption across block boundaries matches single-pass openssl.
     */
    public function testAesCtrChunkedMatchesOpenssl(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(16);

        $plaintext = str_repeat('Q', 80);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $chunked = '';
        $chunked .= $ctx->apply(Byte\slice($plaintext, 0, 7));
        $chunked .= $ctx->apply(Byte\slice($plaintext, 7, 25));
        $chunked .= $ctx->apply(Byte\slice($plaintext, 32, 1));
        $chunked .= $ctx->apply(Byte\slice($plaintext, 33, 47));

        $reference = openssl_encrypt($plaintext, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);

        static::assertSame($reference, $chunked);
    }

    public function testKeystreamBufferSubstrCorrectness(): void
    {
        $keyBytes = SecureRandom\bytes(32);
        $key = new StreamCipher\Key($keyBytes);
        $iv = SecureRandom\bytes(16);

        $ctx = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);

        $result = $ctx->apply('A');
        static::assertSame(1, Byte\length($result), 'Applying 1 byte should produce exactly 1 byte of output');

        $result2 = $ctx->apply('BC');
        static::assertSame(2, Byte\length($result2), 'Applying 2 bytes should produce exactly 2 bytes of output');

        // Verify correctness: single-pass vs byte-by-byte should match
        $ctx2 = new StreamCipher\Context($key, $iv, StreamCipher\Algorithm::Aes256Ctr);
        $singlePass = $ctx2->apply('ABC');
        static::assertSame(
            $singlePass,
            $result . $result2,
            'Byte-by-byte encryption must match single-pass encryption',
        );
    }
}
