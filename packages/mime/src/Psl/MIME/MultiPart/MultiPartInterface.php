<?php

declare(strict_types=1);

namespace Psl\MIME\MultiPart;

use Psl\MIME\Part\PartInterface;

/**
 * Common interface for multipart MIME body builders.
 *
 * Extends {@see PartInterface} so that a multipart body can itself be nested
 * as a part within another multipart structure. Implementations produce a
 * serialized multipart body stream via {@see PartInterface::body()}, with
 * each sub-part delimited by the boundary string.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-5.1
 *
 * @see Composite for multipart/mixed (attachments).
 * @see Alternative for multipart/alternative (different representations).
 * @see Related for multipart/related (inline referenced parts).
 * @see Form for multipart/form-data (HTML form submissions).
 *
 * @api
 */
interface MultiPartInterface extends PartInterface
{
    /**
     * The boundary string used to delimit parts in the serialized body.
     *
     * The boundary is included in the Content-Type "boundary" parameter and
     * must be unique enough not to appear in any part body. Per RFC 2046,
     * boundaries must be 1-70 characters from a restricted character set.
     *
     * @var non-empty-string
     */
    public string $boundary { get; }
}
