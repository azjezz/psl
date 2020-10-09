<?php

declare(strict_types=1);

namespace Psl\Xml\Internal;

use LibXMLError;
use Psl\Xml\Issue\Level;

/**
 * @internal
 */
function issue_level_from_xml_error(LibXMLError $error): ?Level
{
    switch ($error->level) {
        case LIBXML_ERR_ERROR:
            return Level::error();
        case LIBXML_ERR_FATAL:
            return Level::fatal();
        case LIBXML_ERR_WARNING:
            return Level::warning();
    }

    return null;
}
