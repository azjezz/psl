<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\IRI;

$iri = IRI\parse('https://münchen.de/straße');

$uri = $iri->toURI();

// "https://xn--mnchen-3ya.de/stra%C3%9Fe"
IO\write_line('%s', $uri->toString());
