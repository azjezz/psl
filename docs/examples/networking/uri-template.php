<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URI\Template;

$template = Template\parse('https://api.example.com/users/{id}/repos{?sort,page}');

$uri = $template->expand([
    'id' => '42',
    'sort' => 'stars',
    'page' => '2',
]);

// "https://api.example.com/users/42/repos?sort=stars&page=2"
IO\write_line('%s', $uri->toString());
