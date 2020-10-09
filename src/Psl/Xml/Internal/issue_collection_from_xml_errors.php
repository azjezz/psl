<?php

declare(strict_types=1);

namespace Psl\Xml\Internal;

use LibXMLError;
use Psl\Arr;
use Psl\Xml\Issue\Issue;
use Psl\Xml\Issue\IssueCollection;

/**
 * @internal
 *
 * @psalm-param list<LibXMLError> $errors
 */
function issue_collection_from_xml_errors(array $errors): IssueCollection
{
    return new IssueCollection(
        ...Arr\filter_nulls(Arr\map(
            $errors,
            static fn(LibXMLError $error): ?Issue => issue_from_xml_error($error)
        ))
    );
}
