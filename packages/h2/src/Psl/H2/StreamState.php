<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\H2\Frame\FrameType;

/**
 * HTTP/2 stream lifecycle states as defined in RFC 9113 Section 5.1.
 *
 * Streams are independent, bidirectional sequences of frames exchanged within an HTTP/2
 * connection. Each stream goes through a lifecycle of states, driven by the sending and
 * receiving of specific frame types and flags. Transitions between states are triggered
 * by events such as sending/receiving HEADERS, END_STREAM, RST_STREAM, and PUSH_PROMISE frames.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
 */
enum StreamState
{
    /**
     * Idle: the initial state before any frames are exchanged.
     *
     * All streams start in this state. In this state, the following transitions are valid:
     * - Sending or receiving a HEADERS frame causes the stream to become "open".
     * - Sending a PUSH_PROMISE on another stream reserves the stream in "reserved (local)".
     * - Receiving a PUSH_PROMISE on another stream reserves the stream in "reserved (remote)".
     *
     * Receiving any frame other than HEADERS or PRIORITY on a stream in this state MUST
     * be treated as a connection error of type PROTOCOL_ERROR.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case Idle;

    /**
     * Reserved (local): the stream has been promised by the local endpoint.
     *
     * A stream enters this state when the local endpoint sends a PUSH_PROMISE frame
     * ({@see FrameType::PushPromise}) that reserves the stream. In this state, the only
     * allowed transitions are:
     * - Sending a HEADERS frame transitions the stream to "half-closed (remote)".
     * - Sending a RST_STREAM frame transitions the stream to "closed".
     *
     * An endpoint MUST NOT send any frame type other than HEADERS, RST_STREAM, or
     * PRIORITY on a reserved (local) stream.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case ReservedLocal;

    /**
     * Reserved (remote): the stream has been promised by the remote endpoint.
     *
     * A stream enters this state when the remote endpoint sends a PUSH_PROMISE frame
     * ({@see FrameType::PushPromise}) that reserves the stream. In this state, the only
     * allowed transitions are:
     * - Receiving a HEADERS frame transitions the stream to "half-closed (local)".
     * - Receiving a RST_STREAM frame transitions the stream to "closed".
     *
     * An endpoint MAY send a RST_STREAM to reject the pushed stream.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case ReservedRemote;

    /**
     * Open: the stream is active and both endpoints may send frames.
     *
     * A stream in the "open" state may be used by both peers to send frames of any type.
     * Flow-control rules apply to DATA frames sent in this state. From this state:
     * - Sending END_STREAM transitions to "half-closed (local)".
     * - Receiving END_STREAM transitions to "half-closed (remote)".
     * - Sending or receiving RST_STREAM transitions to "closed".
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case Open;

    /**
     * Half-closed (local): the local endpoint has finished sending.
     *
     * The local endpoint has sent a frame with the END_STREAM flag set and can no longer
     * send DATA frames. The local endpoint MUST NOT send frames other than WINDOW_UPDATE,
     * PRIORITY (deprecated), or RST_STREAM in this state. The remote endpoint may still
     * send any type of frame. From this state:
     * - Receiving END_STREAM transitions to "closed".
     * - Sending or receiving RST_STREAM transitions to "closed".
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case HalfClosedLocal;

    /**
     * Half-closed (remote): the remote endpoint has finished sending.
     *
     * The remote endpoint has sent a frame with the END_STREAM flag set and can no longer
     * send data frames. The local endpoint is no longer obligated to maintain a receiver
     * flow-control window for the remote peer. If the local endpoint receives additional
     * frames (other than WINDOW_UPDATE, PRIORITY, or RST_STREAM) for a stream in this state,
     * it MUST respond with a stream error of type STREAM_CLOSED ({@see ErrorCode::StreamClosed}).
     * From this state:
     * - Sending END_STREAM transitions to "closed".
     * - Sending or receiving RST_STREAM transitions to "closed".
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case HalfClosedRemote;

    /**
     * Closed: the stream is fully terminated.
     *
     * The terminal state for a stream. No further frames will be sent on a closed stream,
     * and an endpoint that receives any frame other than PRIORITY on a closed stream MUST
     * treat that as a stream error of type STREAM_CLOSED ({@see ErrorCode::StreamClosed}).
     *
     * A stream enters the "closed" state after:
     * - Both endpoints have sent END_STREAM.
     * - Either endpoint sends or receives a RST_STREAM frame.
     *
     * An endpoint MUST minimally process and then discard frames received on closed streams
     * for a period after sending RST_STREAM to handle frames in transit.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1
     */
    case Closed;
}
