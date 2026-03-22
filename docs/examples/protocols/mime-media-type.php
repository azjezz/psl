<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\MIME\MediaType;

$type = MediaType::parse('application/vnd.api+json; charset=utf-8');

$type->type; // "application"
$type->subtype; // "vnd.api+json"
$type->suffix; // "json"
$type->tree; // "vnd"
$type->parameters; // Parameters with charset=utf-8
$type->essence(); // "application/vnd.api+json"

// From file extension
$json = MediaType::fromExtension('json'); // application/json

// IANA registration
$type->isRegistered(); // true
$type->extensions(); // ["json"]
