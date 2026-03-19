<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Emitted when a SETTINGS frame is received from the remote peer.
 *
 * SETTINGS frames convey configuration parameters that affect how endpoints
 * communicate, such as maximum concurrent streams, initial window size, and
 * header table size.
 *
 * For a SETTINGS ACK (acknowledging previously sent settings), the
 * {@see SettingsReceived::$settings} array will be empty. For a SETTINGS frame
 * carrying new parameters, the array maps setting identifiers (e.g.,
 * SETTINGS_MAX_CONCURRENT_STREAMS) to their values. A SETTINGS ACK is
 * automatically sent in response to non-ACK frames.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113#section-6.5 RFC 9113 Section 6.5 - SETTINGS
 */
final readonly class SettingsReceived implements EventInterface
{
    /**
     * @param array<int, int> $settings Map of setting identifiers to values. Empty for ACK.
     */
    public function __construct(
        public array $settings,
    ) {}
}
