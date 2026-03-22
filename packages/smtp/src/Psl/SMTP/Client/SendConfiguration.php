<?php

declare(strict_types=1);

namespace Psl\SMTP\Client;

use Psl\DateTime\DateTimeInterface;
use Psl\DateTime\Duration;
use Psl\SMTP\DeliverBy;
use Psl\SMTP\Priority;

/**
 * Per-send SMTP extension parameters.
 *
 * Controls optional MAIL FROM parameters for DSN, REQUIRETLS,
 * MT-PRIORITY, DELIVERBY, and FUTURERELEASE extensions.
 *
 * @api
 */
final readonly class SendConfiguration
{
    /**
     * @param null|string $dsnReturn DSN RET parameter: 'FULL' or 'HDRS' (RFC 3461).
     * @param null|string $dsnEnvelopeId DSN ENVID parameter (RFC 3461).
     * @param null|string $dsnNotify DSN NOTIFY parameter: 'NEVER' or combo of 'SUCCESS','FAILURE','DELAY' (RFC 3461).
     * @param bool $requireTls Require TLS for the entire delivery chain (RFC 8689).
     * @param null|Priority $priority Message transfer priority (RFC 6710).
     * @param null|DeliverBy $deliverBy Delivery deadline (RFC 2852).
     * @param null|Duration|DateTimeInterface $futureRelease Deferred delivery: Duration for HOLDFOR, DateTimeInterface for HOLDUNTIL (RFC 4865).
     */
    public function __construct(
        public null|string $dsnReturn = null,
        public null|string $dsnEnvelopeId = null,
        public null|string $dsnNotify = null,
        public bool $requireTls = false,
        public null|Priority $priority = null,
        public null|DeliverBy $deliverBy = null,
        public null|Duration|DateTimeInterface $futureRelease = null,
    ) {}

    /**
     * Return a new instance with the given DSN RET parameter.
     *
     * @param null|string $dsnReturn 'FULL' or 'HDRS' (RFC 3461), or null to disable.
     */
    public function withDsnReturn(null|string $dsnReturn): self
    {
        return new self(
            $dsnReturn,
            $this->dsnEnvelopeId,
            $this->dsnNotify,
            $this->requireTls,
            $this->priority,
            $this->deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given DSN ENVID parameter.
     *
     * @param null|string $dsnEnvelopeId The envelope identifier (RFC 3461), or null to disable.
     */
    public function withDsnEnvelopeId(null|string $dsnEnvelopeId): self
    {
        return new self(
            $this->dsnReturn,
            $dsnEnvelopeId,
            $this->dsnNotify,
            $this->requireTls,
            $this->priority,
            $this->deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given DSN NOTIFY parameter.
     *
     * @param null|string $dsnNotify 'NEVER' or combo of 'SUCCESS','FAILURE','DELAY' (RFC 3461), or null to disable.
     */
    public function withDsnNotify(null|string $dsnNotify): self
    {
        return new self(
            $this->dsnReturn,
            $this->dsnEnvelopeId,
            $dsnNotify,
            $this->requireTls,
            $this->priority,
            $this->deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given REQUIRETLS setting.
     */
    public function withRequireTls(bool $requireTls): self
    {
        return new self(
            $this->dsnReturn,
            $this->dsnEnvelopeId,
            $this->dsnNotify,
            $requireTls,
            $this->priority,
            $this->deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given message transfer priority.
     */
    public function withPriority(null|Priority $priority): self
    {
        return new self(
            $this->dsnReturn,
            $this->dsnEnvelopeId,
            $this->dsnNotify,
            $this->requireTls,
            $priority,
            $this->deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given delivery deadline.
     */
    public function withDeliverBy(null|DeliverBy $deliverBy): self
    {
        return new self(
            $this->dsnReturn,
            $this->dsnEnvelopeId,
            $this->dsnNotify,
            $this->requireTls,
            $this->priority,
            $deliverBy,
            $this->futureRelease,
        );
    }

    /**
     * Return a new instance with the given deferred delivery setting.
     *
     * @param null|Duration|DateTimeInterface $futureRelease Duration for HOLDFOR, DateTimeInterface for HOLDUNTIL (RFC 4865), or null to disable.
     */
    public function withFutureRelease(null|Duration|DateTimeInterface $futureRelease): self
    {
        return new self(
            $this->dsnReturn,
            $this->dsnEnvelopeId,
            $this->dsnNotify,
            $this->requireTls,
            $this->priority,
            $this->deliverBy,
            $futureRelease,
        );
    }
}
