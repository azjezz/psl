<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;

Message\METHOD_GET; // "GET"
Message\METHOD_POST; // "POST"
Message\METHOD_PUT; // "PUT"
Message\METHOD_DELETE; // "DELETE"
Message\METHOD_PATCH; // "PATCH"

Message\STATUS_OK; // 200
Message\STATUS_CREATED; // 201
Message\STATUS_NO_CONTENT; // 204
Message\STATUS_NOT_FOUND; // 404
Message\STATUS_INTERNAL_SERVER_ERROR; // 500

Message\reason_phrase(Message\STATUS_OK); // "OK"
Message\reason_phrase(Message\STATUS_NOT_FOUND); // "Not Found"
Message\reason_phrase(Message\STATUS_TOO_MANY_REQUESTS); // "Too Many Requests"
Message\reason_phrase(999); // "status code 999"
