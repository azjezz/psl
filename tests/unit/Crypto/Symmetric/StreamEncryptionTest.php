<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Crypto\Symmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Symmetric;
use Psl\IO;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Tests\Fixture\SocketLikeReadHandle;

final class StreamEncryptionTest extends TestCase
{
    public function testEncryptDecryptRoundtrip(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = 'Hello, streaming encryption!';
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();
        $encryptor->copyOpened($encrypted, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testLargeDataEncryptDecrypt(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = Str\repeat('A', 100_000);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 4096);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();
        $encryptor->copyOpened($encrypted, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testDecryptWithWrongKeyFails(): void
    {
        $key1 = Symmetric\generate_key();
        $key2 = Symmetric\generate_key();

        $source = new IO\MemoryHandle('test data');
        $encrypted = new IO\MemoryHandle();

        new Symmetric\StreamEncryptor($key1)->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();

        $this->expectException(Exception\DecryptionException::class);
        new Symmetric\StreamEncryptor($key2)->copyOpened($encrypted, $decrypted);
    }

    public function testEmptyStreamEncryptDecrypt(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle('');
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();
        $encryptor->copyOpened($encrypted, $decrypted);

        $decrypted->seek(0);
        static::assertSame('', $decrypted->readAll());
    }

    public function testSmallChunkSize(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = 'This message will be split into very small chunks for encryption.';
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 8);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();
        $encryptor->copyOpened($encrypted, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testVariousChunkSizes(): void
    {
        $key = Symmetric\generate_key();
        $plaintext = Str\repeat('Z', 10_000);

        foreach ([16, 64, 256, 1024, 8192] as $chunkSize) {
            $encryptor = new Symmetric\StreamEncryptor($key);
            $source = new IO\MemoryHandle($plaintext);
            $encrypted = new IO\MemoryHandle();

            $encryptor->copySealed($source, $encrypted, $chunkSize);

            $encrypted->seek(0);
            $decrypted = new IO\MemoryHandle();
            $encryptor->copyOpened($encrypted, $decrypted);

            $decrypted->seek(0);
            static::assertSame($plaintext, $decrypted->readAll(), "Failed with chunk size {$chunkSize}");
        }
    }

    public function testDecryptTamperedStreamFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle('secret data');
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $data = $encrypted->readAll();
        $pos = Symmetric\STREAM_HEADER_BYTES + 4 + 1;
        $data[$pos] = Byte\chr(Byte\ord($data[$pos]) ^ 0xFF);

        $tampered = new IO\MemoryHandle($data);
        $decrypted = new IO\MemoryHandle();

        $this->expectException(Exception\DecryptionException::class);
        $encryptor->copyOpened($tampered, $decrypted);
    }

    public function testDecryptTruncatedStreamFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle(Str\repeat('A', 1000));
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $data = $encrypted->readAll();
        $truncated = new IO\MemoryHandle(Byte\slice($data, 0, (int) (Byte\length($data) / 2)));

        $decrypted = new IO\MemoryHandle();

        $this->expectException(Exception\DecryptionException::class);
        $encryptor->copyOpened($truncated, $decrypted);
    }

    public function testStreamEncryptionProducesDifferentOutputEachTime(): void
    {
        $key = Symmetric\generate_key();
        $plaintext = 'same message';

        $enc1 = new IO\MemoryHandle();
        new Symmetric\StreamEncryptor($key)->copySealed(new IO\MemoryHandle($plaintext), $enc1);

        $enc2 = new IO\MemoryHandle();
        new Symmetric\StreamEncryptor($key)->copySealed(new IO\MemoryHandle($plaintext), $enc2);

        $enc1->seek(0);
        $enc2->seek(0);
        static::assertNotSame($enc1->readAll(), $enc2->readAll());
    }

    public function testBinaryDataStreamRoundtrip(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = SecureRandom\bytes(5000);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 512);

        $encrypted->seek(0);
        $decrypted = new IO\MemoryHandle();
        $encryptor->copyOpened($encrypted, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testDecryptSocketLikeSourceWithShortFinalChunk(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = 'Short final chunk';
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 8192);

        $encrypted->seek(0);
        $ciphertextBytes = $encrypted->readAll();

        $socketSource = new SocketLikeReadHandle($ciphertextBytes);
        $decrypted = new IO\MemoryHandle();

        $encryptor->copyOpened($socketSource, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testDecryptSocketLikeSourceWithMultipleChunks(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = Str\repeat('X', 8192 + 500);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 8192);

        $encrypted->seek(0);
        $ciphertextBytes = $encrypted->readAll();

        $socketSource = new SocketLikeReadHandle($ciphertextBytes);
        $decrypted = new IO\MemoryHandle();

        $encryptor->copyOpened($socketSource, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }
}
