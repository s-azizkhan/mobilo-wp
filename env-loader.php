<?php
class DotEnv
{
    public function __construct(string $path)
    {
        try {
            if (empty($path)) {
                $path = __DIR__ . '/.env';
            }
            if (!file_exists($path)) {
                throw new Exception("Env file not found");
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        } catch (Exception $e) {
            error_log("Failed to load environment variables: " . $e->getMessage());
        }
    }
}
// Usage:
(new DotEnv(__DIR__ . '/.env'));

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
