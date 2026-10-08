<?php
/**
 * AkaziNet .env loader.
 *
 * Required by config/database.php, which must be loaded before any constant is
 * read from the environment. Kept in its own file so the parsing rules can be
 * tested (tests/cases/03_helpers.php) without a database connection.
 *
 * Precedence, highest first:
 *   1. The real process environment (getenv / SetEnv / php.ini).
 *   2. A .env file one directory ABOVE the project root.
 *   3. A .env file inside the project root.
 *   4. The default passed by the caller.
 *
 * (2) is preferred over (3) on purpose: a file above the docroot cannot be
 * downloaded even if .htaccess protection is ever lost.
 */

/**
 * Parse the KEY=VALUE lines of a .env file.
 *
 * Deliberately small: no variable expansion, no backslash escapes inside
 * quoted values. A database password is copied through byte for byte, so any
 * transformation here is a chance to corrupt it.
 *
 * @return array<string, string>
 */
function env_parse_file($path)
{
    $values = [];

    if (!is_string($path) || !is_file($path) || !is_readable($path)) {
        return $values;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $values;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // `export FOO=bar` is valid in a real shell environment, so accept it.
        if (strpos($line, 'export ') === 0) {
            $line = trim(substr($line, 7));
        }

        $separator = strpos($line, '=');
        if ($separator === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separator));
        if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $key)) {
            continue;
        }

        $value = trim(substr($line, $separator + 1));

        if ($value === '') {
            $values[$key] = '';
            continue;
        }

        $quote = $value[0];
        $isQuoted = ($quote === '"' || $quote === "'")
            && strlen($value) >= 2
            && substr($value, -1) === $quote;

        if ($isQuoted) {
            // A quoted value keeps any '#' inside it, which is the whole reason
            // quoting is supported: passwords contain '#'.
            $values[$key] = substr($value, 1, -1);
            continue;
        }

        // Unquoted: a '#' preceded by whitespace starts a trailing comment.
        // `KEY=a#b` stays intact because there is no space before the '#'.
        $hash = strpos($value, ' #');
        if ($hash !== false) {
            $value = rtrim(substr($value, 0, $hash));
        }

        $values[$key] = $value;
    }

    return $values;
}

/**
 * The parsed .env file, read once per request.
 *
 * Static caching matters here: config/database.php calls env() for about a
 * dozen constants, and re-reading the file that many times would be wasteful.
 *
 * @return array<string, string>
 */
function env_file_values()
{
    static $loaded = false;
    static $values = [];

    if ($loaded) {
        return $values;
    }
    $loaded = true;

    // One directory above the project root first: outside the docroot, so it
    // cannot be served even if the rules protecting .env are lost.
    $candidates = [
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $values = env_parse_file($candidate);
            return $values;
        }
    }

    return $values;
}

/**
 * Read a configuration value.
 *
 * An empty value is returned as-is rather than replaced by the default, because
 * DB_PASS= is a legitimate empty password. Callers wanting a default on empty
 * should pass it to env_bool()/env_int(), which treat '' as unset.
 *
 * @return mixed
 */
function env($key, $default = '')
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    $file = env_file_values();
    if (array_key_exists($key, $file)) {
        return $file[$key];
    }

    return $default;
}

/**
 * Read a boolean setting. Truthy: 1, true, yes, on (any case).
 *
 * An unset or blank value yields the default, which is what lets a .env file
 * carry `APP_DEBUG=` and mean "off" rather than "fall through to true".
 */
function env_bool($key, $default = false)
{
    $value = getenv($key);

    if ($value === false) {
        $file = env_file_values();
        if (!array_key_exists($key, $file)) {
            return (bool)$default;
        }
        $value = $file[$key];
    }

    if (is_bool($value)) {
        return $value;
    }

    $normalized = strtolower(trim((string)$value));
    if ($normalized === '') {
        return (bool)$default;
    }

    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

/**
 * Read an integer setting. Anything non-numeric (including '5 ' with letters,
 * 'true', or an accidental note) yields the default rather than 0.
 */
function env_int($key, $default = 0)
{
    $value = getenv($key);

    if ($value === false) {
        $file = env_file_values();
        if (!array_key_exists($key, $file)) {
            return (int)$default;
        }
        $value = $file[$key];
    }

    if (is_int($value)) {
        return $value;
    }

    $value = trim((string)$value);
    if ($value === '' || !preg_match('/^-?\d+$/', $value)) {
        return (int)$default;
    }

    return (int)$value;
}

/**
 * Define a constant from an environment setting unless it already exists.
 *
 * The defined-if-absent check lets a host set DB_NAME as a real PHP constant
 * (some cPanel panels do this) and have it win over .env.
 */
function env_define($constant, $key, $default = '')
{
    if (defined($constant)) {
        return;
    }
    define($constant, env($key, $default));
}