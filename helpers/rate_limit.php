<?php

class RateLimitException extends Exception
{
    /**
     * @var int
     */
    private $retryAfter;

    public function __construct(string $message, int $retryAfter)
    {
        parent::__construct($message);
        $this->retryAfter = max(1, $retryAfter);
    }

    public function getRetryAfter(): int
    {
        return (int) $this->retryAfter;
    }
}

function rate_limit_storage_path(): string
{
    $directory = sys_get_temp_dir();
    return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'pracear_login_rate_limit.json';
}

function rate_limit_with_storage(callable $callback)
{
    $path = rate_limit_storage_path();
    $handle = fopen($path, 'c+');

    if ($handle === false) {
        throw new RuntimeException('No se pudo acceder al almacenamiento de control de velocidad.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el almacenamiento de control de velocidad.');
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        $data = [];

        if (is_string($contents) && $contents !== '') {
            $decoded = json_decode($contents, true);

            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $result = null;
        $exception = null;

        try {
            $result = $callback($data);
        } catch (Throwable $throwable) {
            $exception = $throwable;
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT);
        if ($encoded === false) {
            throw new RuntimeException('No se pudo serializar el almacenamiento de control de velocidad.');
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $encoded);

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        return $result;
    } finally {
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function rate_limit_prune_attempts(array $attempts, int $now, int $interval): array
{
    $filtered = [];

    foreach ($attempts as $timestamp) {
        $timestamp = (int) $timestamp;

        if ($timestamp >= ($now - $interval)) {
            $filtered[] = $timestamp;
        }
    }

    return array_values($filtered);
}

function rate_limit_assert_can_attempt(string $ip, ?string $login, array $config): void
{
    $ip = trim($ip) !== '' ? $ip : 'unknown';
    $now = time();

    rate_limit_with_storage(function (&$data) use ($ip, $login, $config, $now) {
        $ipConfig = $config['ip'];
        $accountConfig = $config['account'];

        if (!isset($data['ip'][$ip]['failures'])) {
            $data['ip'][$ip]['failures'] = [];
        }

        $data['ip'][$ip]['failures'] = rate_limit_prune_attempts(
            $data['ip'][$ip]['failures'],
            $now,
            (int) $ipConfig['interval_seconds']
        );

        if (count($data['ip'][$ip]['failures']) >= (int) $ipConfig['max_attempts']) {
            $oldest = (int) min($data['ip'][$ip]['failures']);
            $retryAfter = ($oldest + (int) $ipConfig['interval_seconds']) - $now;
            throw new RateLimitException('Se superó el límite de intentos para esta dirección IP. Inténtelo más tarde.', max(1, $retryAfter));
        }

        if ($login !== null && $login !== '') {
            if (!isset($data['account'][$login])) {
                $data['account'][$login] = [
                    'failures' => [],
                    'streak' => 0,
                    'last_failure' => null,
                    'lock_until' => null,
                ];
            }

            $accountData = $data['account'][$login];
            $accountData['failures'] = rate_limit_prune_attempts(
                $accountData['failures'],
                $now,
                (int) $accountConfig['interval_seconds']
            );

            $lockUntil = isset($accountData['lock_until']) ? (int) $accountData['lock_until'] : null;
            if ($lockUntil !== null && $lockUntil <= $now) {
                $accountData['lock_until'] = null;
                $lockUntil = null;
            }

            if ($lockUntil !== null && $lockUntil > $now) {
                $retryAfter = $lockUntil - $now;
                $data['account'][$login] = $accountData;
                throw new RateLimitException('La cuenta se encuentra temporalmente bloqueada. Inténtelo de nuevo más tarde.', max(1, $retryAfter));
            }

            if (count($accountData['failures']) >= (int) $accountConfig['max_attempts']) {
                $oldest = (int) min($accountData['failures']);
                $retryAfter = ($oldest + (int) $accountConfig['interval_seconds']) - $now;
                throw new RateLimitException('Se superó el límite de intentos para esta cuenta. Inténtelo de nuevo más tarde.', max(1, $retryAfter));
            }

            $data['account'][$login] = $accountData;
        }
    });
}

function rate_limit_register_failure(string $ip, ?string $login, array $config): void
{
    $ip = trim($ip) !== '' ? $ip : 'unknown';
    $now = time();

    rate_limit_with_storage(function (&$data) use ($ip, $login, $config, $now) {
        $ipConfig = $config['ip'];
        $accountConfig = $config['account'];
        $backoffConfig = $config['backoff'];

        if (!isset($data['ip'][$ip]['failures'])) {
            $data['ip'][$ip]['failures'] = [];
        }

        $data['ip'][$ip]['failures'] = rate_limit_prune_attempts(
            $data['ip'][$ip]['failures'],
            $now,
            (int) $ipConfig['interval_seconds']
        );
        $data['ip'][$ip]['failures'][] = $now;

        if ($login !== null && $login !== '') {
            if (!isset($data['account'][$login])) {
                $data['account'][$login] = [
                    'failures' => [],
                    'streak' => 0,
                    'last_failure' => null,
                    'lock_until' => null,
                ];
            }

            $accountData = $data['account'][$login];
            $accountData['failures'] = rate_limit_prune_attempts(
                $accountData['failures'],
                $now,
                (int) $accountConfig['interval_seconds']
            );
            $accountData['failures'][] = $now;

            $lastFailure = isset($accountData['last_failure']) ? (int) $accountData['last_failure'] : null;
            if ($lastFailure !== null && ($now - $lastFailure) <= (int) $backoffConfig['streak_reset_seconds']) {
                $accountData['streak'] = (int) $accountData['streak'] + 1;
            } else {
                $accountData['streak'] = 1;
            }

            $accountData['last_failure'] = $now;

            if (!empty($backoffConfig['enabled']) && $accountData['streak'] >= (int) $backoffConfig['start_after_failures']) {
                $exponent = $accountData['streak'] - (int) $backoffConfig['start_after_failures'];
                $delay = (int) ($backoffConfig['base_seconds'] * pow(2, max(0, $exponent)));
                $delay = min($delay, (int) $backoffConfig['max_seconds']);
                $lockUntil = $now + $delay;
                if (!isset($accountData['lock_until']) || $lockUntil > (int) $accountData['lock_until']) {
                    $accountData['lock_until'] = $lockUntil;
                }
            } else {
                $accountData['lock_until'] = null;
            }

            $data['account'][$login] = $accountData;
        }
    });
}

function rate_limit_register_success(string $ip, ?string $login, array $config): void
{
    $ip = trim($ip) !== '' ? $ip : 'unknown';

    rate_limit_with_storage(function (&$data) use ($ip, $login) {
        if (isset($data['ip'][$ip])) {
            unset($data['ip'][$ip]);
        }

        if ($login !== null && $login !== '' && isset($data['account'][$login])) {
            unset($data['account'][$login]);
        }
    });
}
