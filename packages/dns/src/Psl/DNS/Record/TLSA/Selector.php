<?php

declare(strict_types=1);

namespace Psl\DNS\Record\TLSA;

/**
 * TLSA selector field values per RFC 6698.
 *
 * @api
 */
enum Selector: int
{
    /**
     * Match against the full DER-encoded certificate.
     */
    case FullCert = 0;

    /**
     * Match against the DER-encoded SubjectPublicKeyInfo structure.
     */
    case SubjectPublicKeyInfo = 1;
}
