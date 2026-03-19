<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\H2\Frame\FrameType;

/**
 * The HTTP/2 client connection preface string (24 octets).
 *
 * This is the magic string "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n" that MUST be sent by the
 * client as the first bytes of the connection. It is designed to elicit a deterministic
 * error from HTTP/1.1 servers that do not support HTTP/2, serving as a protocol detection
 * mechanism. The server connection preface consists of a SETTINGS frame
 * ({@see FrameType::Settings}), which may be empty.
 *
 * Value: "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n" (24 bytes).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4
 *
 * @var string
 */
const CONNECTION_PREFACE = "PRI * HTTP/2.0\r\n\r\nSM\r\n\r\n";

/**
 * The fixed size of an HTTP/2 frame header, in bytes.
 *
 * Every HTTP/2 frame begins with a 9-octet header containing:
 * - Length (3 octets): the length of the frame payload, not including the header.
 * - Type (1 octet): the frame type ({@see FrameType}).
 * - Flags (1 octet): frame-type-specific boolean flags.
 * - Reserved (1 bit): a reserved bit that MUST remain unset (0x0).
 * - Stream Identifier (31 bits): the stream this frame is associated with.
 *
 * Value: 9 bytes.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-4.1
 *
 * @var int
 */
const FRAME_HEADER_SIZE = 9;

/**
 * The default initial flow-control window size for new streams, in octets.
 *
 * When a new stream is created or when SETTINGS_INITIAL_WINDOW_SIZE
 * ({@see Setting::InitialWindowSize}) has not been explicitly set, both the stream-level
 * and connection-level flow-control windows start at this value. The connection-level
 * window can be updated via WINDOW_UPDATE frames ({@see FrameType::WindowUpdate}).
 *
 * Value: 65,535 octets (2^16 - 1).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.9.2
 */
const DEFAULT_INITIAL_WINDOW_SIZE = 65_535;

/**
 * The default maximum frame payload size, in octets.
 *
 * This is the initial value of the SETTINGS_MAX_FRAME_SIZE ({@see Setting::MaxFrameSize})
 * parameter. All implementations MUST be capable of receiving frames of at least this size.
 * The value can be increased up to {@see MAX_FRAME_SIZE_UPPER_BOUND} via a SETTINGS frame.
 *
 * Value: 16,384 octets (2^14).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 */
const DEFAULT_MAX_FRAME_SIZE = 16_384;

/**
 * The maximum allowed value for SETTINGS_MAX_FRAME_SIZE, in octets.
 *
 * This is the upper bound for the maximum frame payload size that an endpoint can advertise.
 * The SETTINGS_MAX_FRAME_SIZE ({@see Setting::MaxFrameSize}) parameter MUST be between
 * {@see DEFAULT_MAX_FRAME_SIZE} (2^14) and this value (2^24 - 1) inclusive. Values outside
 * this range MUST be treated as a connection error of type PROTOCOL_ERROR.
 *
 * Value: 16,777,215 octets (2^24 - 1).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 */
const MAX_FRAME_SIZE_UPPER_BOUND = 16_777_215;

/**
 * The default HPACK dynamic table size, in octets.
 *
 * This is the initial value of the SETTINGS_HEADER_TABLE_SIZE ({@see Setting::HeaderTableSize})
 * parameter. It specifies the maximum size of the HPACK dynamic table that the decoder
 * will use. The encoder can choose to use a smaller table by sending a dynamic table size
 * update in a field block.
 *
 * Value: 4,096 octets.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 * @link https://datatracker.ietf.org/doc/html/rfc7541#section-4.2
 */
const DEFAULT_HEADER_TABLE_SIZE = 4_096;

/**
 * The default maximum number of concurrent streams.
 *
 * This is the initial value used when SETTINGS_MAX_CONCURRENT_STREAMS
 * ({@see Setting::MaxConcurrentStreams}) has not been explicitly set. Per RFC 9113, there
 * is no limit until the setting is received; this implementation uses 2^31 - 1 to represent
 * "effectively unlimited" while staying within a signed 32-bit integer range.
 *
 * It is recommended that this value be no smaller than 100 to avoid unnecessarily
 * limiting parallelism.
 *
 * Value: 2,147,483,647 (2^31 - 1).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 */
const DEFAULT_MAX_CONCURRENT_STREAMS = 2_147_483_647;

/**
 * The default maximum header list size, representing unlimited.
 *
 * This is the initial value used when SETTINGS_MAX_HEADER_LIST_SIZE
 * ({@see Setting::MaxHeaderListSize}) has not been explicitly set. Per RFC 9113, the
 * initial value is unlimited; this implementation uses PHP_INT_MAX to represent that
 * there is no practical limit on the size of the header list.
 *
 * The header list size is calculated as the sum of the size of each field line, where
 * each line contributes the length of its name, plus the length of its value, plus
 * an overhead of 32 octets.
 *
 * Value: PHP_INT_MAX (platform-dependent; typically 2^63 - 1 on 64-bit systems).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5.2
 */
const DEFAULT_MAX_HEADER_LIST_SIZE = PHP_INT_MAX;

/**
 * The maximum flow-control window size, in octets.
 *
 * This is the largest value that a flow-control window can reach. A WINDOW_UPDATE frame
 * ({@see FrameType::WindowUpdate}) that causes the flow-control window to exceed this
 * value MUST be treated as a connection error of type FLOW_CONTROL_ERROR
 * ({@see ErrorCode::FlowControlError}) for the connection window, or a stream error
 * for a stream window.
 *
 * Value: 2,147,483,647 (2^31 - 1).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.9
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.9.1
 */
const MAX_WINDOW_SIZE = 2_147_483_647;

/**
 * The maximum stream identifier value.
 *
 * Stream identifiers are 31-bit unsigned integers. Stream 0x0 is reserved for
 * connection-level frames (SETTINGS, PING, GOAWAY, and connection-level WINDOW_UPDATE).
 * Client-initiated streams use odd identifiers (1, 3, 5, ...) and server-initiated
 * streams use even identifiers (2, 4, 6, ...). A stream identifier cannot be reused;
 * once exhausted, a new connection must be established.
 *
 * Value: 2,147,483,647 (0x7FFFFFFF, 2^31 - 1).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1.1
 */
const MAX_STREAM_ID = 0x7FFF_FFFF;
