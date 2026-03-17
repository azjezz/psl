<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\IRI;
use Psl\URL;

$iri = IRI\parse('https://münchen.de/straße');
$url = URL\from_iri($iri);

// "https://xn--mnchen-3ya.de/stra%C3%9Fe"
IO\write_line('%s', $url->toString());
