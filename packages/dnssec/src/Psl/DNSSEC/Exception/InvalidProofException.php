<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Exception;

use Throwable;

/**
 * Thrown when a DNSSEC denial-of-existence proof is invalid, incomplete, or missing.
 * Covers NSEC, NSEC3, and missing proof failures.
 *
 * @api
 */
final class InvalidProofException extends RuntimeException
{
    /**
     * The domain name for which the proof failed, if applicable.
     */
    public readonly null|string $queryName;

    /**
     * The unsupported NSEC3 hash algorithm number, if applicable.
     */
    public readonly null|int $algorithm;

    private function __construct(
        string $message,
        null|string $queryName = null,
        null|int $algorithm = null,
        null|Throwable $previous = null,
    ) {
        $this->queryName = $queryName;
        $this->algorithm = $algorithm;
        parent::__construct($message, $previous);
    }

    /**
     * Create an exception when no NSEC record covers the query name.
     */
    public static function forNameNotCovered(string $queryName): self
    {
        return new self('No NSEC record covers the query name \'' . $queryName . '\'.', queryName: $queryName);
    }

    /**
     * Create an exception when a wildcard record exists, invalidating the NXDOMAIN proof.
     */
    public static function forWildcardExists(string $wildcard, string $queryName): self
    {
        return new self(
            'Wildcard \'' . $wildcard . '\' exists; NXDOMAIN proof invalid for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC record covers the wildcard for the query name.
     */
    public static function forWildcardNotCovered(string $queryName): self
    {
        return new self('No NSEC record covers the wildcard for \'' . $queryName . '\'.', queryName: $queryName);
    }

    /**
     * Create an exception when an NSEC record includes the queried record type.
     */
    public static function forTypeExists(string $queryName, string $type): self
    {
        return new self('NSEC record for \'' . $queryName . '\' includes type ' . $type . '.', queryName: $queryName);
    }

    /**
     * Create an exception when no NSEC record matches the query name.
     */
    public static function forNameNotMatched(string $queryName): self
    {
        return new self('No NSEC record matches the query name \'' . $queryName . '\'.', queryName: $queryName);
    }

    /**
     * Create an exception when no closest encloser is found in NSEC3 records.
     */
    public static function forNoClosestEncloser(string $queryName): self
    {
        return new self(
            'No closest encloser found in NSEC3 records for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC3 record covers the next closer name.
     */
    public static function forNextCloserNotCovered(string $queryName): self
    {
        return new self(
            'No NSEC3 record covers the next closer name for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC3 record covers the wildcard at the closest encloser.
     */
    public static function forNSEC3WildcardNotCovered(string $queryName): self
    {
        return new self(
            'No NSEC3 record covers the wildcard at closest encloser for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when an NSEC3 record includes the queried record type.
     */
    public static function forNSEC3TypeExists(string $queryName, string $type): self
    {
        return new self('NSEC3 record for \'' . $queryName . '\' includes type ' . $type . '.', queryName: $queryName);
    }

    /**
     * Create an exception when no NSEC3 record matches the query name hash.
     */
    public static function forNSEC3NameNotMatched(string $queryName): self
    {
        return new self(
            'No NSEC3 record matches the query name hash for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC3 proof of DS non-existence exists.
     */
    public static function forDSNotProven(string $queryName): self
    {
        return new self('No NSEC3 proof of DS non-existence for \'' . $queryName . '\'.', queryName: $queryName);
    }

    /**
     * Create an exception when NSEC3 hash computation fails.
     */
    public static function forComputationFailed(string $detail, Throwable $previous): self
    {
        return new self('NSEC3 hash computation failed: ' . $detail . '.', previous: $previous);
    }

    /**
     * Create an exception when NSEC3 parameters are inconsistent across records.
     */
    public static function forInconsistentParameters(): self
    {
        return new self('NSEC3 parameters are inconsistent across records.');
    }

    /**
     * Create an exception for an unsupported NSEC3 hash algorithm.
     */
    public static function forUnsupportedHashAlgorithm(int $algorithm): self
    {
        return new self('Unsupported NSEC3 hash algorithm: ' . $algorithm . '.', algorithm: $algorithm);
    }

    /**
     * Create an exception when no RRSIG records are found for a query name.
     */
    public static function forRRSIG(string $queryName): self
    {
        return new self('No RRSIG records found for \'' . $queryName . '\'.', queryName: $queryName);
    }

    /**
     * Create an exception when no NSEC/NSEC3 records are found for an NXDOMAIN proof.
     */
    public static function forNSECProof(string $queryName): self
    {
        return new self(
            'No NSEC or NSEC3 records found for NXDOMAIN proof of \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC/NSEC3 records are found for a NODATA proof.
     */
    public static function forNODATAProof(string $queryName): self
    {
        return new self(
            'No NSEC or NSEC3 records found for NODATA proof of \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when no NSEC/NSEC3 records are found for DS non-existence proof.
     */
    public static function forDSProof(string $queryName): self
    {
        return new self(
            'No NSEC or NSEC3 records found for DS non-existence proof of \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when a signed negative response is missing NSEC/NSEC3 proof.
     */
    public static function forSignedResponse(string $queryName): self
    {
        return new self(
            'Signed negative response missing NSEC/NSEC3 proof for \'' . $queryName . '\'.',
            queryName: $queryName,
        );
    }

    /**
     * Create an exception when the DNSKEY record count exceeds safety limits.
     */
    public static function forExcessiveDNSKEYCount(int $count): self
    {
        return new self('DNS response contains ' . $count . ' DNSKEY records, exceeding safety limit.');
    }

    /**
     * Create an exception when the RRSIG record count exceeds safety limits.
     */
    public static function forExcessiveRRSIGCount(int $count): self
    {
        return new self('DNS response contains ' . $count . ' RRSIG records, exceeding safety limit.');
    }

    /**
     * Create an exception when the NSEC3 iteration count exceeds the allowed maximum.
     */
    public static function forExcessiveNSEC3Iterations(int $iterations, int $limit): self
    {
        return new self('NSEC3 iteration count ' . $iterations . ' exceeds maximum of ' . $limit . '.');
    }
}
