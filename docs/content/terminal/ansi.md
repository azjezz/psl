# Ansi

The `Ansi` component provides pure functions for constructing ANSI escape sequences -- text styling, colors, cursor movement, screen manipulation, hyperlinks, and terminal mode control.

All functions return immutable sequence objects that implement `CommandInterface`. No I/O is performed -- call `toString()` to get the raw escape string for writing to a terminal.

## Usage

@example('terminal/ansi-styling.php')

## Design

The component is organized around three sequence types that implement `CommandInterface`:

- **`ControlSequenceIntroducer`** (CSI) -- sequences starting with `\e[`, used for cursor movement, text styling (SGR), screen erasing, scrolling, and DEC private mode toggling.
- **`OperatingSystemCommand`** (OSC) -- sequences starting with `\e]`, used for setting window titles, hyperlinks, clipboard access, and desktop notifications.
- **`ControlCharacter`** -- single-byte control characters (e.g. BEL `\x07`) that don't fit the CSI or OSC format.

All are `final readonly` classes with a `toString()` method that produces the raw escape string. Functions in the component return one of these types (or a plain `string` for composed output like `apply()` and `link()`).

## Text Styling

The `apply()` function wraps text with SGR (Select Graphic Rendition) sequences and an automatic reset. It accepts any combination of styles and colors:

@example('terminal/ansi-text-styling.php')

Colors are created through factory functions in the `Color` namespace and passed to `foreground()` or `background()`:

- **16 named colors** -- `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`, and their `bright_*` variants
- **ANSI-256** -- `Color\ansi256(int $code)` for the 256-color palette
- **24-bit RGB** -- `Color\rgb(int $r, int $g, int $b)` for true color
- **Hex** -- `Color\hex(string $hex)` for hex color strings like `'#FF6600'`

## Hyperlinks

The `link()` function wraps text in an OSC 8 hyperlink, with optional SGR styles applied to the visible text:

@example('terminal/ansi-hyperlinks.php')

Terminal emulators that support OSC 8 render the text as a clickable link.

## Cursor Control

Functions in the `Cursor` namespace manipulate cursor position and visibility:

@example('terminal/ansi-cursor.php')

## Screen Control

Functions in the `Screen` namespace control the display, window properties, and terminal modes.

@example('terminal/ansi-screen.php')

## Examples

### Styled Log Output

@example('terminal/ansi-log-output.php')

### TUI Setup and Teardown

@example('terminal/ansi-tui-setup.php')

See `src/Psl/Ansi/` for the full API.
