<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Crypto\Symmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Symmetric;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Tests\Fixture\SocketLikeReadHandle;
use Psl\Tests\Fixture\TrickleReadHandle;

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

    public function testDecryptTrickleSourceOneByte(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = 'Trickle test with partial frame headers';
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 16);

        $encrypted->seek(0);
        $ciphertextBytes = $encrypted->readAll();

        $trickleSource = new TrickleReadHandle($ciphertextBytes, 1);
        $decrypted = new IO\MemoryHandle();

        $encryptor->copyOpened($trickleSource, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testDecryptTrickleSourceTwoBytes(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = Str\repeat('Y', 500);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();

        $encryptor->copySealed($source, $encrypted, 64);

        $encrypted->seek(0);
        $ciphertextBytes = $encrypted->readAll();

        $trickleSource = new TrickleReadHandle($ciphertextBytes, 2);
        $decrypted = new IO\MemoryHandle();

        $encryptor->copyOpened($trickleSource, $decrypted);

        $decrypted->seek(0);
        static::assertSame($plaintext, $decrypted->readAll());
    }

    public function testDecryptInvalidFrameSizeFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle('test');
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted, 16);

        $encrypted->seek(0);
        $data = $encrypted->readAll();

        $frameLengthOffset = Symmetric\STREAM_HEADER_BYTES + 4;
        $oversizedFrame = pack('V', 99_999);
        $corrupted =
            Byte\slice($data, 0, $frameLengthOffset) . $oversizedFrame . Byte\slice($data, $frameLengthOffset + 4);

        $corruptedSource = new IO\MemoryHandle($corrupted);
        $decrypted = new IO\MemoryHandle();

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Invalid frame size in stream.');
        $encryptor->copyOpened($corruptedSource, $decrypted);
    }

    public function testDecryptInvalidChunkSizeZeroFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle('test');
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $data = $encrypted->readAll();

        $corrupted =
            Byte\slice($data, 0, Symmetric\STREAM_HEADER_BYTES)
            . pack('V', 0)
            . Byte\slice($data, Symmetric\STREAM_HEADER_BYTES + 4);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Invalid chunk size in stream header.');
        $encryptor->copyOpened(new IO\MemoryHandle($corrupted), new IO\MemoryHandle());
    }

    public function testDecryptTruncatedFrameHeaderFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $source = new IO\MemoryHandle('test data for truncation');
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted);

        $encrypted->seek(0);
        $data = $encrypted->readAll();

        $truncated = Byte\slice($data, 0, Symmetric\STREAM_HEADER_BYTES + 4 + 2);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Stream decryption failed: truncated frame header.');
        $encryptor->copyOpened(new TrickleReadHandle($truncated, 1), new IO\MemoryHandle());
    }

    public function testDecryptStreamWithoutFinalTagFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = Str\repeat('A', 100);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted, 16);

        $encrypted->seek(0);
        $data = $encrypted->readAll();

        $pos = Symmetric\STREAM_HEADER_BYTES + 4;
        $lastFrameStart = $pos;
        while ($pos < Byte\length($data)) {
            $lastFrameStart = $pos;
            /** @var int $frameLen */
            $frameLen = unpack('V', Byte\slice($data, $pos, 4))[1];
            $pos += 4 + $frameLen;
        }

        $truncated = Byte\slice($data, 0, $lastFrameStart);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Stream ended without final tag.');
        $encryptor->copyOpened(new IO\MemoryHandle($truncated), new IO\MemoryHandle());
    }

    public function testDecryptStreamWithoutFinalTagNonEofSourceFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\StreamEncryptor($key);

        $plaintext = Str\repeat('A', 100);
        $source = new IO\MemoryHandle($plaintext);
        $encrypted = new IO\MemoryHandle();
        $encryptor->copySealed($source, $encrypted, 16);

        $encrypted->seek(0);
        $data = $encrypted->readAll();

        $pos = Symmetric\STREAM_HEADER_BYTES + 4;
        $lastFrameStart = $pos;
        while ($pos < Byte\length($data)) {
            $lastFrameStart = $pos;
            /** @var int $frameLen */
            $frameLen = unpack('V', Byte\slice($data, $pos, 4))[1];
            $pos += 4 + $frameLen;
        }

        $truncated = Byte\slice($data, 0, $lastFrameStart);

        $handle = new readonly class($truncated) implements IO\ReadHandleInterface {
            private IO\MemoryHandle $inner;

            public function __construct(string $data)
            {
                $this->inner = new IO\MemoryHandle($data);
            }

            public function reachedEndOfDataSource(): bool
            {
                return false; // never reports EOF
            }

            public function tryRead(null|int $max_bytes = null): string
            {
                return $this->inner->tryRead($max_bytes);
            }

            public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
            {
                return $this->inner->read($max_bytes, $timeout);
            }

            public function readAll(null|int $max_bytes = null, null|Duration $timeout = null): string
            {
                return $this->inner->readAll($max_bytes, $timeout);
            }

            public function readFixedSize(int $size, null|Duration $timeout = null): string
            {
                return $this->inner->readFixedSize($size, $timeout);
            }
        };

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Stream ended without final tag.');
        $encryptor->copyOpened($handle, new IO\MemoryHandle());
    }
}
