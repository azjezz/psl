<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

/**
 * Return the standard reason phrase for an HTTP status code.
 *
 * Maps the given numeric HTTP status code to its canonical reason phrase as
 * defined by RFC 9110 Section 15 and the IANA HTTP Status Code Registry.
 * This is primarily used for HTTP/1.x status line serialization, where the
 * status line takes the form "HTTP/1.1 200 OK". HTTP/2 (RFC 9113) and
 * HTTP/3 (RFC 9114) do not transmit reason phrases on the wire.
 *
 * For unrecognized status codes (those not in the standard registry), the
 * function returns a generic string of the form "status code {N}" rather
 * than throwing an exception, ensuring forward compatibility with newly
 * registered or experimental status codes.
 *
 * @param int $statusCode The HTTP status code to look up (e.g., 200, 404, 500). Use the status code constants from {@see constants.php} for clarity.
 *
 * @return non-empty-string The canonical reason phrase for the given status code, or "status code {N}" for unrecognized codes.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15 Status Codes
 * @link https://www.iana.org/assignments/http-status-codes IANA HTTP Status Code Registry
 * @link https://datatracker.ietf.org/doc/html/rfc9112#section-4 HTTP/1.1 Status Line
 *
 * @api
 *
 * @pure
 *
 * @mago-expect analysis:deprecated-constant
 */
function reason_phrase(int $statusCode): string
{
    return match ($statusCode) {
        namespace\STATUS_CONTINUE => 'Continue',
        namespace\STATUS_SWITCHING_PROTOCOLS => 'Switching Protocols',
        namespace\STATUS_PROCESSING => 'Processing',
        namespace\STATUS_EARLY_HINTS => 'Early Hints',
        namespace\STATUS_OK => 'OK',
        namespace\STATUS_CREATED => 'Created',
        namespace\STATUS_ACCEPTED => 'Accepted',
        namespace\STATUS_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        namespace\STATUS_NO_CONTENT => 'No Content',
        namespace\STATUS_RESET_CONTENT => 'Reset Content',
        namespace\STATUS_PARTIAL_CONTENT => 'Partial Content',
        namespace\STATUS_MULTI_STATUS => 'Multi-Status',
        namespace\STATUS_ALREADY_REPORTED => 'Already Reported',
        namespace\STATUS_IM_USED => 'IM Used',
        namespace\STATUS_MULTIPLE_CHOICES => 'Multiple Choices',
        namespace\STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        namespace\STATUS_FOUND => 'Found',
        namespace\STATUS_SEE_OTHER => 'See Other',
        namespace\STATUS_NOT_MODIFIED => 'Not Modified',
        namespace\STATUS_USE_PROXY => 'Use Proxy',
        namespace\STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        namespace\STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        namespace\STATUS_BAD_REQUEST => 'Bad Request',
        namespace\STATUS_UNAUTHORIZED => 'Unauthorized',
        namespace\STATUS_PAYMENT_REQUIRED => 'Payment Required',
        namespace\STATUS_FORBIDDEN => 'Forbidden',
        namespace\STATUS_NOT_FOUND => 'Not Found',
        namespace\STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        namespace\STATUS_NOT_ACCEPTABLE => 'Not Acceptable',
        namespace\STATUS_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        namespace\STATUS_REQUEST_TIMEOUT => 'Request Timeout',
        namespace\STATUS_CONFLICT => 'Conflict',
        namespace\STATUS_GONE => 'Gone',
        namespace\STATUS_LENGTH_REQUIRED => 'Length Required',
        namespace\STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        namespace\STATUS_CONTENT_TOO_LARGE => 'Content Too Large',
        namespace\STATUS_URI_TOO_LONG => 'URI Too Long',
        namespace\STATUS_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        namespace\STATUS_RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
        namespace\STATUS_EXPECTATION_FAILED => 'Expectation Failed',
        namespace\STATUS_IM_A_TEAPOT => "I'm a Teapot",
        namespace\STATUS_MISDIRECTED_REQUEST => 'Misdirected Request',
        namespace\STATUS_UNPROCESSABLE_CONTENT => 'Unprocessable Content',
        namespace\STATUS_LOCKED => 'Locked',
        namespace\STATUS_FAILED_DEPENDENCY => 'Failed Dependency',
        namespace\STATUS_TOO_EARLY => 'Too Early',
        namespace\STATUS_UPGRADE_REQUIRED => 'Upgrade Required',
        namespace\STATUS_PRECONDITION_REQUIRED => 'Precondition Required',
        namespace\STATUS_TOO_MANY_REQUESTS => 'Too Many Requests',
        namespace\STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        namespace\STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        namespace\STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        namespace\STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        namespace\STATUS_BAD_GATEWAY => 'Bad Gateway',
        namespace\STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',
        namespace\STATUS_GATEWAY_TIMEOUT => 'Gateway Timeout',
        namespace\STATUS_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        namespace\STATUS_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        namespace\STATUS_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        namespace\STATUS_LOOP_DETECTED => 'Loop Detected',
        namespace\STATUS_NOT_EXTENDED => 'Not Extended',
        namespace\STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        default => 'status code ' . $statusCode,
    };
}
