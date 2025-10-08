<?php
declare(strict_types=1);

if (!function_exists('load_project_env')) {
    /**
     * Load key/value pairs from a .env style file.
     *
     * @return array<string, string>
     */
    function load_project_env(string $baseDir, string $fileName = '.env'): array
    {
        $path = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (!is_readable($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $values = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $delimiterPosition = strpos($line, '=');
            if ($delimiterPosition === false) {
                continue;
            }

            $key = trim(substr($line, 0, $delimiterPosition));
            $value = trim(substr($line, $delimiterPosition + 1));

            if ($key === '') {
                continue;
            }

            if ($value !== '') {
                $firstCharacter = $value[0];
                $lastCharacter = substr($value, -1);
                if ((($firstCharacter === '"' && $lastCharacter === '"') || ($firstCharacter === "'" && $lastCharacter === "'")) && strlen($value) >= 2) {
                    $value = substr($value, 1, -1);
                }
            }

            $values[$key] = $value;
        }

        return $values;
    }
}

if (!function_exists('get_env_value')) {
    /**
     * Retrieve an environment variable. Environment variables take precedence over file values.
     */
    function get_env_value(string $key, array $fileValues, mixed $default = null): mixed
    {
        $environmentValue = getenv($key);
        if ($environmentValue !== false) {
            return $environmentValue;
        }

        if (array_key_exists($key, $fileValues)) {
            return $fileValues[$key];
        }

        return $default;
    }
}
