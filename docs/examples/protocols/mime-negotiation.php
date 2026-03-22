<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\MIME\MediaPreferences;
use Psl\MIME\MediaType;

$prefs = MediaPreferences::parse('text/html, application/json;q=0.9, */*;q=0.1');

$best = $prefs->negotiate([
    new MediaType('application', 'json'),
    new MediaType('text', 'html'),
]);

// $best->essence() === "text/html"
