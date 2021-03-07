<?php

declare(strict_types = 1);

function generateNamespaceLine($namespace)
{
    static $last_namespace = null;

    if ($namespace !== $last_namespace) {
        $last_namespace = $namespace;
        return "\n" . $namespace . "  \n";//double space for new line
    }

    return '';
}

function generateLink($version, $filename, $startLine): string
{
    $string = "https://github.com/azjezz/psl/blob/%s/src/%s#L%d";

    return  sprintf(
        $string,
        $version,
        $filename,
        $startLine
    );
}

function normaliseFilename($filename): string
{
    $lastPos = strrpos($filename, 'psl/src');
    if ($lastPos !== false) {
        $filename = substr($filename, $lastPos + strlen('psl/src/'));
    }
    return $filename;
}

function generateFunctionLine($sha, $function): string
{
    $rf = new ReflectionFunction($function);

    $filename = $rf->getFileName();
    $filename = normaliseFilename($filename);

    $link = generateLink($sha, $filename, $rf->getStartLine());
    $rf = new ReflectionFunction($function);

    $line = generateNamespaceLine($rf->getNamespaceName());

    $functionWithoutNamespace = str_replace($rf->getNamespaceName(), '', $function);
    $functionWithoutNamespace = ltrim($functionWithoutNamespace, '\\');

    $line .= sprintf(
        "* <a href='%s'>%s</a>  ", //double space for new line
        $link,
        $functionWithoutNamespace
    );

    return $line;
}

function generateInterfaceLine($sha, $interface): string
{
    $rc = new ReflectionClass($interface);

    $filename = $rc->getFileName();
    $filename = normaliseFilename($filename);

    $link = generateLink($sha, $filename, $rc->getStartLine());

    $line = generateNamespaceLine($rc->getNamespaceName());

    $functionWithoutNamespace = str_replace($rc->getNamespaceName(), '', $interface);
    $functionWithoutNamespace = ltrim($functionWithoutNamespace, '\\');

    $line .= sprintf(
        "* <a href='%s'>%s</a>  ", //double space for new line
        $link,
        $functionWithoutNamespace
    );

    return $line;
}

function generateDocList(): string
{
    $version = '1.5.x';

    require_once __DIR__ . "/vendor/autoload.php";

    $contents = [];
    $contents[] = '## Functions';
    $contents[] = '';
    foreach (\Psl\Internal\Loader::FUNCTIONS as $function) {
        $contents[] = generateFunctionLine($version, $function);
    }

    $contents[] = '';
    $contents[] = '## Interfaces';
    $contents[] = '';
    foreach (\Psl\Internal\Loader::INTERFACES as $interface) {
        $contents[] = generateInterfaceLine($version, $interface);
    }

    $contents[] = '';
    $contents[] = '## Classes';
    $contents[] = '';
    foreach (\Psl\Internal\Loader::CLASSES as $interface) {
        $contents[] = generateInterfaceLine($version, $interface);
    }

    $contents[] = '---';
    $contents[] = 'This markdown file was generated from doc_gen.php. Any edits to it will likely be lost.';

    return implode("\n", $contents);
}


$contents = <<< MD
# Documentation

This doc contains a list of the functions, interfaces and classes this library provides.

Please click through to read the docblock comment details for each of them.

MD;

$contents .= generateDocList();

function checkDocsUpToDate($docFilename, $contents)
{
    $newLines = explode("\n", $contents);
    $existingLines = file($docFilename);

    if (count($newLines) !== count($existingLines)) {
        echo "Docs are out of date, please regenerate by running 'php doc_gen.php'. Number of lines doesn't match.\n";
        exit(-1);
    }

    for ($i = 0; $i < count($newLines); $i += 1) {
        if (trim($newLines[$i]) !== trim($existingLines[$i])) {
            echo "Docs are out of date, please regenerate by running 'php doc_gen.php'. Difference on line $i.\n";
            echo "generated: [" . $newLines[$i] . "]\n";
            echo "checkedin: [" . $existingLines[$i] . "]\n";
            exit(-1);
        }
    }

    echo "docs are  up to date.\n";
    exit(0);
}

$docFilename = __DIR__ . "/docs/index.md";

if ($argc >= 2) {
    $firstArg = $argv[1];
    if (strcasecmp($firstArg, 'check') === 0) {
        checkDocsUpToDate($docFilename, $contents);
    }
    echo "Unknown arg '$firstArg'.\n";
    exit(-1);
}

file_put_contents($docFilename, $contents);