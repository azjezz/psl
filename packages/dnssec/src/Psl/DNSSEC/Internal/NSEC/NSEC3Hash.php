<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Internal\NSEC;

use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Internal\Encoder;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\Hash;

use function hex2bin;
use function strtolower;

/**
 * Computes NSEC3 hashed owner names per RFC 5155 Section 5.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc5155#section-5
 *
 * @internal
 */
final class NSEC3Hash
{
    /**
     * Compute the NSEC3 hash of a domain name.
     *
     * IH(salt, x, 0) = H(x || salt)
     * IH(salt, x, k) = H(IH(salt, x, k-1) || salt)
     *
     * @param string $name The domain name to hash.
     * @param int $algorithm The hash algorithm (1 = SHA-1).
     * @param int $iterations The number of additional hash iterations.
     * @param string $salt The hex-encoded salt value.
     *
     * @return string Raw binary hash, or empty string if algorithm is unsupported.
     *
     * @throws InvalidProofException If the iteration count exceeds the maximum allowed or hashing fails.
     * @throws InvalidArgumentException If the domain name is invalid.
     */
    public static function compute(string $name, int $algorithm, int $iterations, string $salt): string
    {
        if ($iterations > 100) {
            throw InvalidProofException::forExcessiveNSEC3Iterations($iterations, 100);
        }

        if ($algorithm !== 1) {
            return '';
        }

        try {
            $wireOwner = Encoder::encodeName(strtolower($name));
            $saltBinary = $salt !== '' ? hex2bin($salt) : '';
            if (false === $saltBinary) {
                $saltBinary = '';
            }

            $hexHash = Hash\hash($wireOwner . $saltBinary, Hash\Algorithm::Sha1);
            $hash = hex2bin($hexHash);
            if (false === $hash) {
                $hash = '';
            }

            for ($i = 0; $i < $iterations; $i++) {
                $hexHash = Hash\hash($hash . $saltBinary, Hash\Algorithm::Sha1);
                $hash = hex2bin($hexHash);
                if (false === $hash) {
                    $hash = '';
                }
            }

            return $hash;
        } catch (Hash\Exception\RuntimeException $e) {
            throw InvalidProofException::forComputationFailed($e->getMessage(), $e);
        }
    }
}
