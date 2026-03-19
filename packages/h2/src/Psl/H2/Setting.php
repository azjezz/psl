<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\H2\Frame\FrameType;

/**
 * HTTP/2 settings parameters as defined in RFC 9113 Section 6.5.2.
 *
 * Settings parameters are conveyed in SETTINGS frames ({@see FrameType::Settings}) and
 * establish constraints on peer behavior. Each setting has an identifier and a 32-bit
 * value. A SETTINGS frame can contain any number of parameters, and parameters may appear
 * in any order. An endpoint that receives a SETTINGS frame with unknown or unsupported
 * identifiers MUST ignore those settings.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 */
enum Setting: int
{
    /**
     * SETTINGS_HEADER_TABLE_SIZE (0x1): HPACK dynamic table size.
     *
     * Maximum size of the HPACK dynamic table used to decode field blocks, in octets.
     * The encoder can select any size equal to or less than this value by using signaling
     * specific to the field compression format inside a field block.
     *
     * Default: 4,096 octets ({@see DEFAULT_HEADER_TABLE_SIZE}).
     * Valid range: 0 to 2^32 - 1.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     * @link https://datatracker.ietf.org/doc/html/rfc7541#section-4.2
     */
    case HeaderTableSize = 0x1;

    /**
     * SETTINGS_ENABLE_PUSH (0x2): Server push toggle.
     *
     * Indicates whether the remote endpoint is permitted to send PUSH_PROMISE frames
     * ({@see FrameType::PushPromise}). A value of 0 disables server push; a value of 1
     * enables it. A client MUST NOT send a PUSH_PROMISE frame. A server that receives a
     * SETTINGS frame with ENABLE_PUSH set to a value other than 0 or 1 MUST treat this
     * as a connection error of type PROTOCOL_ERROR.
     *
     * Default: 1 (server push enabled).
     * Valid values: 0 (disabled), 1 (enabled).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     */
    case EnablePush = 0x2;

    /**
     * SETTINGS_MAX_CONCURRENT_STREAMS (0x3): Maximum concurrent streams.
     *
     * Maximum number of concurrent streams that the sender will allow. This limit is
     * directional: it applies to the number of streams that the receiver of the setting
     * can initiate. It is recommended that this value be no smaller than 100 to avoid
     * unnecessarily limiting parallelism. A value of 0 prevents the creation of new streams.
     *
     * Default: no limit ({@see DEFAULT_MAX_CONCURRENT_STREAMS}, effectively unlimited).
     * Valid range: 0 to 2^31 - 1 (the maximum stream identifier).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     */
    case MaxConcurrentStreams = 0x3;

    /**
     * SETTINGS_INITIAL_WINDOW_SIZE (0x4): Stream-level flow-control window size.
     *
     * Initial window size in octets for stream-level flow control. This setting affects
     * the window size of all streams, including existing streams. Values above 2^31 - 1
     * ({@see MAX_WINDOW_SIZE}) MUST be treated as a connection error of type
     * FLOW_CONTROL_ERROR ({@see ErrorCode::FlowControlError}).
     *
     * Default: 65,535 octets ({@see DEFAULT_INITIAL_WINDOW_SIZE}, 2^16 - 1).
     * Valid range: 0 to 2^31 - 1 (2,147,483,647).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.9.2
     */
    case InitialWindowSize = 0x4;

    /**
     * SETTINGS_MAX_FRAME_SIZE (0x5): Maximum frame payload size.
     *
     * Maximum size of a frame payload the sender is willing to receive, in octets.
     * The initial value MUST be between 2^14 (16,384, {@see DEFAULT_MAX_FRAME_SIZE}) and
     * 2^24 - 1 (16,777,215, {@see MAX_FRAME_SIZE_UPPER_BOUND}) octets inclusive. Values
     * outside this range MUST be treated as a connection error of type PROTOCOL_ERROR.
     *
     * Default: 16,384 octets ({@see DEFAULT_MAX_FRAME_SIZE}, 2^14).
     * Valid range: 16,384 to 16,777,215 (2^14 to 2^24 - 1).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     */
    case MaxFrameSize = 0x5;

    /**
     * SETTINGS_MAX_HEADER_LIST_SIZE (0x6): Maximum header list size.
     *
     * Maximum size of the field section (header list) the sender is prepared to accept,
     * in octets. The value is based on the uncompressed size of field lines, including
     * the length of the name, value, and a per-field overhead of 32 octets. If not set,
     * the default is unlimited ({@see DEFAULT_MAX_HEADER_LIST_SIZE}).
     *
     * This is advisory: an endpoint MAY send field sections that exceed this limit but
     * the peer MAY treat such a request or response as malformed.
     *
     * Default: unlimited ({@see DEFAULT_MAX_HEADER_LIST_SIZE}).
     * Valid range: 0 to 2^32 - 1.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
     */
    case MaxHeaderListSize = 0x6;

    /**
     * SETTINGS_ENABLE_CONNECT_PROTOCOL (0x8): Extended CONNECT toggle.
     *
     * Indicates whether the server supports the extended CONNECT method
     * as defined in RFC 8441. When set to 1, the client may send CONNECT
     * requests with a `:protocol` pseudo-header to bootstrap protocols
     * like WebSocket over HTTP/2 streams.
     *
     * Default: 0 (disabled).
     * Valid values: 0 (disabled), 1 (enabled).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8441
     */
    case EnableConnectProtocol = 0x8;
}
