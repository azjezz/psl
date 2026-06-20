<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum Color
{
    case Red;
    case Green;
    case Blue;
}

interface Renderable
{
    public function render(): string;
}

class HtmlRenderer implements Renderable
{
    public function render(): string
    {
        return '<p>Hello</p>';
    }
}

// Validate class instances
$value = new DateTimeImmutable();
Type\instance_of::<DateTimeImmutable>(DateTimeImmutable::class)->assert($value);

// Backed enums -- coerce from the backing value
Type\backed_enum::<Status>(Status::class)->coerce('active');

// Unit enums -- only accept enum instances directly
Type\unit_enum::<Color>(Color::class)->assert(Color::Red);

// Class strings
Type\class_string::<Renderable>(Renderable::class)->assert(HtmlRenderer::class);
