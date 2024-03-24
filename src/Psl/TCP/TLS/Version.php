<?php

declare(strict_types=1);

namespace Psl\TCP\TLS;

/**
 * Represents the TLS protocol versions supported for secure TCP connections.
 */
enum Version: string
{
    /**
     * Represents TLS version 1.0.
     *
     * @note This version is considered obsolete and insecure. Its use is
     * generally not recommended unless absolutely necessary for compatibility.
     */
    case Tls10 = 'TLSv1.0';

    /**
     * Represents TLS version 1.1.
     *
     * @note This version is also considered obsolete and should be used
     * with caution, preferably only for compatibility with legacy systems.
     */
    case Tls11 = 'TLSv1.1';

    /**
     * Represents TLS version 1.2.
     *
     * This version is widely supported and offers a good balance of compatibility
     * and security. It introduces several security enhancements over the previous
     * versions and is suitable for most applications.
     */
    case Tls12 = 'TLSv1.2';

    /**
     * Represents TLS version 1.3.
     *
     * This is the most recent version of the TLS protocol (as of this writing)
     * and offers improved security and performance. TLS 1.3 simplifies the
     * protocol and removes outdated features, making it the recommended choice
     * for new applications.
     */
    case Tls13 = 'TLSv1.3';

    /**
     * Returns the default TLS version for secure connections.
     *
     * This method provides a convenient way to select a default TLS version
     * for applications. It currently defaults to TLS version 1.2, considering its
     * wide support and balance between compatibility and security. This default
     * may be updated in future to reflect changes in best practices and protocol
     * security standards.
     *
     * @pure
     */
    public static function default(): Version
    {
        return self::Tls12;
    }
}
