<?php

declare(strict_types=1);

namespace Psl\Xml\Internal;

use LibXMLError;
use Psl\Exception\InvariantViolationException;
use Psl\Result;
use Psl\Result\ResultInterface;
use Psl\Xml\Issue\IssueCollection;

use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;

/**
 * You can wrap this detector around a function that interacts with xml, dom, simplexml, ...
 * It wil return a result that contains the result or an exception if an error was encountered.
 * Besides the result, you will receive a list of XML errors.
 * No XML warnings will be displayed in the meantime.
 *
 * @internal
 *
 * @template Tr
 *
 * @psalm-param callable(): Tr $run
 * @psalm-return array{ResultInterface<Tr>, IssueCollection}
 *
 * @throws InvariantViolationException
 */
function detect_errors(callable $run): array
{
    $previousErrorReporting = libxml_use_internal_errors(true);
    libxml_clear_errors();

    $result = Result\wrap($run);

    /** @var list<LibXMLError> $errors */
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previousErrorReporting);

    return [$result, issue_collection_from_xml_errors($errors)];
}
