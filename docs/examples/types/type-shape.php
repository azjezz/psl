<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$shape = Type\shape([
    'name' => Type\string(),
    'articles' => Type\vec(Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
        'likes' => Type\int(),
        'comments' => Type\optional(Type\vec(Type\shape([
            'user' => Type\string(),
            'comment' => Type\string(),
        ]))),
    ])),
    'pagination' => Type\optional(Type\shape([
        'currentPage' => Type\uint(),
        'totalPages' => Type\uint(),
        'perPage' => Type\uint(),
        'totalRows' => Type\uint(),
    ])),
]);

$untrustedData = [
    'name' => 'Alice',
    'articles' => [
        [
            'title' => 'Hello World',
            'content' => 'My first article.',
            'likes' => 5,
            'comments' => [
                ['user' => 'Bob', 'comment' => 'Great post!'],
            ],
        ],
    ],
    'pagination' => [
        'currentPage' => 1,
        'totalPages' => 10,
        'perPage' => 20,
        'totalRows' => 200,
    ],
];

$validData = $shape->coerce($untrustedData);
