<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\Record\SSHFP\Algorithm;
use Psl\DNS\Record\SSHFP\FingerprintType;

/**
 * An SSHFP record (RFC 4255) containing an SSH host key fingerprint.
 *
 * @api
 */
final class SSHFPRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::SSHFP;
    }

    /**
     * @param string          $name            The domain name this record belongs to.
     * @param Duration        $duration        The time-to-live for this record.
     * @param Algorithm       $algorithm       The public key algorithm.
     * @param FingerprintType $fingerprintType The fingerprint hash type.
     * @param string          $fingerprint     The hex-encoded fingerprint of the host key.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly Algorithm $algorithm,
        public readonly FingerprintType $fingerprintType,
        public readonly string $fingerprint,
    ) {}
}
