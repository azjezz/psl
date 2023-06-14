<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

enum Variant
{
    case Default;
    case UrlSafe;
    case DotSlash;
    case DotSlashOrdered;
}
