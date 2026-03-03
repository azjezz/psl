<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto;
use Psl\Crypto\Exception;
use Psl\IO;

interface StreamEncryptorInterface extends Crypto\StreamEncryptorInterface
{
    /**
     * Encrypt a stream of data.
     *
     * @param positive-int $chunkSize The size of each plaintext chunk to process.
     *
     * @throws Exception\RuntimeException If encryption fails.
     */
    public function copySealed(
        IO\ReadHandleInterface $source,
        IO\WriteHandleInterface $destination,
        int $chunkSize = 8192,
    ): void;

    /**
     * Decrypt a stream of data.
     *
     * @throws Exception\DecryptionException If decryption fails.
     */
    public function copyOpened(IO\ReadHandleInterface $source, IO\WriteHandleInterface $destination): void;
}
