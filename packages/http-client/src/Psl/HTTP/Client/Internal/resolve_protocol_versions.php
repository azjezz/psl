<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;

use function in_array;

/**
 * Determine which HTTP protocol versions to use for a request.
 *
 * Reconciles the request's explicit protocol version (if non-default) with the
 * client configuration's preference list:
 *
 * - If the request specifies a non-default version (anything other than HTTP/1.1),
 *   it is treated as an explicit requirement and must be in the configuration's
 *   allowed list.
 * - If the request uses the default (HTTP/1.1), the configuration's full
 *   preference list is returned.
 * - HTTP/3 is not yet supported and always throws.
 *
 * @param Request $request The HTTP request (inspected for protocolVersion).
 * @param ClientConfiguration $configuration The client configuration (inspected for protocolVersions).
 *
 * @return list<ProtocolVersion> The effective protocol version preference list.
 *
 * @throws ProtocolException If HTTP/3 is requested (not yet supported), the
 *     requested version is not in the configuration, or no versions are configured.
 *
 * @internal
 */
function resolve_protocol_versions(Request $request, ClientConfiguration $configuration): array
{
    $configVersions = $configuration->protocolVersions;
    $requestVersion = $request->protocolVersion;

    if ($requestVersion === ProtocolVersion::V30 || in_array(ProtocolVersion::V30, $configVersions, strict: true)) {
        throw ProtocolException::forUnsupportedProtocol(ProtocolVersion::V30);
    }

    // Non-default request version: treat as explicit requirement.
    if ($requestVersion !== ProtocolVersion::V11) {
        if (!in_array($requestVersion, $configVersions, strict: true)) {
            throw ProtocolException::forUnsupportedProtocol($requestVersion);
        }

        return [$requestVersion];
    }

    if ($configVersions === []) {
        throw ProtocolException::forMalformedResponse('No protocol versions configured.');
    }

    return $configVersions;
}
