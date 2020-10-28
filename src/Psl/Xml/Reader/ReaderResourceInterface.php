<?php

declare(strict_types=1);

namespace Psl\Xml\Reader;

use Psl\File\Exception\FileNotFoundException;
use XMLReader;

/**
 * This resource is bugging me.
 * There is no way to pass a 'resource' into the XMLReader at the moment.
 *
 * Therefore, it is not possible to use a IO ReadHandle
 * unless we do some bad stuff like streaming it to a temp file and read that file.
 *
 * This resource interface could be removed if we added these named constructors on the reader interface.
 * Both are not optimal .. but neither is life!
 *
 * @see https://www.php.net/manual/en/xmlreader.open.php
 * @see https://www.php.net/manual/en/xmlreader.xml.php
 */
interface ReaderResourceInterface
{
    public static function fromXml(string $xml): self;

    /**
     * It is important this function checks if a file exists.
     * Otherwise it is possible to read a remote url, which is not optimal.
     * Mainly because remote url's require additional stream contexts etc.
     *
     * @throws FileNotFoundException
     */
    public static function fromFile(string $file): self;

    /**
     * Downside : This would leak the XMLReader from libxml... Which sucks
     * Other option are
     *
     *  - configure(XMLReader) which would change an existing resource in a mutable way - not optimal
     *  - getUri(): ?string & getXml(): ?string -> not optimal either ...
     *  - Remove this class all together and create a named constructor on the reader interface instead.
     *
     * We could in the future add additional features into this resource class:
     *
     * - xsd
     * - dtd
     * - ...
     *
     * @see https://www.php.net/manual/en/xmlreader.setparserproperty.php
     * @see https://www.php.net/manual/en/xmlreader.setrelaxngschema.php
     * @see https://www.php.net/manual/en/xmlreader.setrelaxngschemasource.php
     * @see https://www.php.net/manual/en/xmlreader.setschema.php
     */
    public function build(): XMLReader;
}
