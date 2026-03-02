# Locale

The `Locale` component provides a backed enum containing 700+ locale identifiers (such as `en_US`, `fr_FR`, `ja_JP`). Using an enum instead of raw strings eliminates typos, enables IDE autocompletion, and makes locale parameters type-safe throughout your application.

## Usage

### Selecting a Locale

Pick a locale by name -- every case maps to its standard locale string:

@example('system/locale-select.php')

### Default Locale

`Locale::default()` reads the locale configured via PHP's `intl.default_locale` setting. If no locale is configured or the configured value is not recognized, it falls back to `Locale::English`:

@example('system/locale-default.php')

### Inspecting a Locale

Each locale instance exposes methods to extract its parts:

@example('system/locale-inspect.php')

Script-based locales also work:

@example('system/locale-script.php')

### Integration with DateTime

The `Locale` enum is used by PSL's `DateTime` formatting to produce locale-aware output:

@example('system/locale-datetime.php')

See `src/Psl/Locale/` for the full API.
