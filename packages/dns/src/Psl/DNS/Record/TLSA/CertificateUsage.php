<?php

declare(strict_types=1);

namespace Psl\DNS\Record\TLSA;

/**
 * TLSA certificate usage field values per RFC 6698.
 *
 * @api
 */
enum CertificateUsage: int
{
    /**
     * CA constraint validated against the PKIX trust chain (Trust Anchor).
     */
    case PKIX_TA = 0;

    /**
     * Service certificate constraint validated against the PKIX trust chain (End Entity).
     */
    case PKIX_EE = 1;

    /**
     * Trust anchor assertion, bypassing PKIX validation (DANE Trust Anchor).
     */
    case DANE_TA = 2;

    /**
     * Domain-issued certificate, bypassing PKIX validation (DANE End Entity).
     */
    case DANE_EE = 3;
}
