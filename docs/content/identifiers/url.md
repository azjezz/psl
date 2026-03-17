# URL

The `URL` component provides a strict URL type that guarantees a scheme and authority are always present. It builds on top of the `URI` and `IRI` packages, adding validation and default port stripping.

## Parsing

Parse a URL string with strict validation; scheme and authority are required:

@example('networking/url-parsing.php')

Default ports are automatically stripped for known schemes (HTTP 80, HTTPS 443, FTP 21, SSH 22, and many more).

## From URI

Convert a parsed `URI` to a `URL` with validation:

@example('networking/url-from-uri.php')

Throws `InvalidURLException` if the URI is missing a scheme, authority, or has a rootless path.

## From IRI

Convert an internationalized IRI directly to a URL, applying Punycode encoding and percent-encoding in one step:

@example('networking/url-from-iri.php')

## Converting Back to URI

A `URL` can be converted back to a `URI` for use with the full URI API:

@example('networking/url-to-uri.php')

See `src/Psl/URL/` for the full API.
