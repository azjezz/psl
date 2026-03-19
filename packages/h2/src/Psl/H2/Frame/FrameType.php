<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

/**
 * HTTP/2 frame types as defined in RFC 9113 Section 6.
 *
 * Each frame type serves a specific purpose in the HTTP/2 protocol. The frame type
 * is an 8-bit value carried in the frame header, determining how the remainder of
 * the frame header and payload are interpreted.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6
 */
enum FrameType: int
{
    /**
     * DATA frame (type=0x0).
     *
     * Conveys arbitrary, variable-length sequences of octets associated with a stream.
     * DATA frames are subject to flow control and can only be sent when the stream is
     * in the "open" or "half-closed (remote)" state. The entire DATA frame payload is
     * included in flow control, including the Pad Length and Padding fields if present.
     *
     * Flags: END_STREAM (0x1), PADDED (0x8).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.1
     */
    case Data = 0x0;

    /**
     * HEADERS frame (type=0x1).
     *
     * Opens a stream and additionally carries a field block fragment. HEADERS frames can
     * be sent on a stream in the "idle", "reserved (local)", "open", or "half-closed (remote)"
     * state. This frame is used to initiate a request or response, and to carry HTTP header fields.
     *
     * Flags: END_STREAM (0x1), END_HEADERS (0x4), PADDED (0x8), PRIORITY (0x20, deprecated).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.2
     */
    case Headers = 0x1;

    /**
     * PRIORITY frame (type=0x2).
     *
     * Specifies the sender-advised priority of a stream. This frame type is deprecated
     * in RFC 9113 and SHOULD NOT be sent. Endpoints MUST NOT use the prioritization
     * signals carried in PRIORITY frames. An endpoint that receives a PRIORITY frame
     * MUST ignore it.
     *
     * Fixed length: 5 octets. Stream identifier MUST be non-zero.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.3
     *
     * @note Deprecated in RFC 9113; priority signaling via PRIORITY frames is no longer recommended.
     */
    case Priority = 0x2;

    /**
     * RST_STREAM frame (type=0x3).
     *
     * Allows immediate termination of a stream. RST_STREAM is sent to request cancellation
     * of a stream or to indicate that an error condition has occurred. The payload contains
     * a single unsigned 32-bit integer identifying the error code ({@see ErrorCode}).
     *
     * Fixed length: 4 octets. Stream identifier MUST be non-zero.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.4
     */
    case RstStream = 0x3;

    /**
     * SETTINGS frame (type=0x4).
     *
     * Conveys configuration parameters that affect how endpoints communicate, such as
     * preferences and constraints on peer behavior. SETTINGS frames are also used to
     * acknowledge receipt of those settings. SETTINGS frames MUST be sent on stream 0
     * and apply to the entire connection, not a single stream.
     *
     * Flags: ACK (0x1). Payload: zero or more setting parameters ({@see Setting}).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.5
     */
    case Settings = 0x4;

    /**
     * PUSH_PROMISE frame (type=0x5).
     *
     * Notifies the peer endpoint in advance of streams the sender intends to initiate.
     * The frame includes the unsigned 31-bit identifier of the stream that the endpoint
     * plans to create along with a field block that provides additional context for the
     * promised stream. PUSH_PROMISE MUST only be sent on a peer-initiated stream that
     * is in the "open" or "half-closed (remote)" state.
     *
     * Flags: END_HEADERS (0x4), PADDED (0x8).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.6
     */
    case PushPromise = 0x5;

    /**
     * PING frame (type=0x6).
     *
     * Measures the minimum round-trip time from the sender and serves as a mechanism
     * to determine whether an idle connection is still functional ("keep-alive").
     * PING frames MUST be sent on stream 0 and have a fixed payload of 8 octets.
     *
     * Flags: ACK (0x1).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.7
     */
    case Ping = 0x6;

    /**
     * GOAWAY frame (type=0x7).
     *
     * Initiates a graceful shutdown of the connection or signals serious error conditions.
     * GOAWAY allows an endpoint to stop accepting new streams while still finishing
     * processing of previously established streams. The payload contains the highest
     * stream identifier the sender may have acted on, and an error code ({@see ErrorCode})
     * indicating the reason for closing the connection.
     *
     * GOAWAY frames MUST be sent on stream 0 and apply to the connection, not a specific stream.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.8
     */
    case GoAway = 0x7;

    /**
     * WINDOW_UPDATE frame (type=0x8).
     *
     * Implements flow control by informing the peer that the sender is ready to receive
     * additional data. Flow control operates at both the individual stream level and the
     * connection level. The payload is a single unsigned 31-bit integer indicating the
     * number of octets the sender can transmit in addition to the existing flow-control window.
     * The legal range for the increment is 1 to 2^31 - 1 (2,147,483,647) octets.
     *
     * Fixed length: 4 octets.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.9
     */
    case WindowUpdate = 0x8;

    /**
     * CONTINUATION frame (type=0x9).
     *
     * Continues a sequence of field block fragments from a preceding HEADERS, PUSH_PROMISE,
     * or CONTINUATION frame. Any number of CONTINUATION frames can be sent, as long as
     * the preceding frame is on the same stream and is a HEADERS, PUSH_PROMISE, or
     * CONTINUATION frame without the END_HEADERS flag set.
     *
     * Flags: END_HEADERS (0x4).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.10
     */
    case Continuation = 0x9;

    /**
     * ALTSVC frame (type=0xa).
     *
     * Advertises alternative services that the origin can be reached at,
     * enabling protocol upgrades (e.g. HTTP/2 to HTTP/3) and load distribution.
     * Sent by servers only. On stream 0, carries an explicit Origin field;
     * on a non-zero stream, applies to the origin of that stream.
     *
     * Payload: 2-byte origin length + origin string + Alt-Svc field value.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7838
     */
    case AltSvc = 0xa;

    /**
     * ORIGIN frame (type=0xc).
     *
     * Declares the set of origins that the server is authoritative for on
     * this connection, enabling connection coalescing - the client can reuse
     * one HTTP/2 connection for requests to multiple domains without additional
     * TLS handshakes. ORIGIN frames MUST be sent on stream 0 and contain a
     * list of serialized origins.
     *
     * Payload: repeated [2-byte origin length + origin string].
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8336
     */
    case Origin = 0xc;

    /**
     * PRIORITY_UPDATE frame (type=0x10).
     *
     * Signals stream priority using the extensible priority scheme defined in RFC 9218.
     * Replaces the deprecated PRIORITY frame mechanism with a more flexible approach
     * based on Structured Fields. PRIORITY_UPDATE frames MUST be sent on stream 0
     * and reference the target stream in the payload.
     *
     * Payload: 4-byte prioritized stream ID + variable-length Priority Field Value.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9218
     */
    case PriorityUpdate = 0x10;
}
