<?php

declare(strict_types=1);

namespace Psl\Crypto;

use Psl\IO;

interface StreamEncryptorInterface
{
    /**
     * Reads plaintext data from the source handle, encrypts it, and writes the sealed ciphertext to the destination handle.
     *
     * The data is processed in chunks to maintain a constant, low memory footprint regardless of the total stream size.
     *
     * @param positive-int $chunkSize The size in bytes of each plaintext chunk to read and process at a time.
     *
     * @throws Exception\RuntimeException If the encryption process fails.
     */
    public function copySealed(
        IO\ReadHandleInterface $source,
        IO\WriteHandleInterface $destination,
        int $chunkSize = 8192,
    ): void;

    /**
     * Reads sealed ciphertext from the source handle, decrypts it, and writes the opened plaintext to the destination handle.
     *
     * The decryption process authenticates the stream on the fly. It will fail immediately if the ciphertext
     * has been tampered with, corrupted, or unexpectedly truncated.
     *
     * @throws Exception\DecryptionException If decryption fails, the stream is corrupted, or authentication tags do not match.
     */
    public function copyOpened(IO\ReadHandleInterface $source, IO\WriteHandleInterface $destination): void;
}
