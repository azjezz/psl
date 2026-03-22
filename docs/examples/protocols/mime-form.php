<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\MIME\MediaType;
use Psl\MIME\MultiPart\Form;
use Psl\MIME\Part;

$form = new Form();
$form->addField('username', 'azjezz');
$form->addField('bio', 'PSL maintainer');
$form->addPart(
    'avatar',
    new Part\Data(new IO\MemoryHandle('image bytes'), filename: 'avatar.png', mediaType: MediaType::parse('image/png')),
);

$form->mediaType; // multipart/form-data; boundary=...
$form->boundary; // random 24-char alphanumeric string
