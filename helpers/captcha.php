<?php

/**
 * Utility functions to generate and validate simple math captcha challenges.
 */
function captcha_ensure_storage(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['captcha']) || !is_array($_SESSION['captcha'])) {
        $_SESSION['captcha'] = [];
    }
}

/**
 * Generates a new captcha challenge for a form key and stores it in the session.
 *
 * @param string $form_key Identifier of the form the captcha belongs to.
 *
 * @return array{question: string, answer: int}
 */
function captcha_refresh(string $form_key): array
{
    captcha_ensure_storage();

    // Obtener IP y login si están disponibles
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $login = isset($_POST['login']) ? $_POST['login'] : null;
    // Cargar configuración de rate limit
    $security_config = include __DIR__ . '/../config/security.php';
    $rate_limit_config = $security_config['rate_limit'];

    // Obtener número de intentos fallidos recientes
    $failures = 0;
    if (file_exists(__DIR__ . '/rate_limit.php')) {
        require_once __DIR__ . '/rate_limit.php';
        $failures = 0;
        try {
            rate_limit_with_storage(function ($data) use ($ip, $login, &$failures) {
                $now = time();
                // Contar fallos por IP
                if (isset($data['ip'][$ip]['failures'])) {
                    foreach ($data['ip'][$ip]['failures'] as $ts) {
                        if ($ts >= ($now - 600)) $failures++;
                    }
                }
                // Contar fallos por login
                if ($login && isset($data['account'][$login]['failures'])) {
                    foreach ($data['account'][$login]['failures'] as $ts) {
                        if ($ts >= ($now - 600)) $failures++;
                    }
                }
            });
        } catch (Exception $e) {
            // Si hay error, ignorar y usar captcha simple
        }
    }

    // Si hay más de 3 intentos fallidos, generar captcha complejo
    if ($failures >= 3) {
        // Generar una expresión matemática compleja
        $ops = ['+', '-', '*'];
        $expr = '';
        $answer = null;
        $num_ops = random_int(2, 3); // 2 o 3 operaciones
        $numbers = [];
        for ($i = 0; $i <= $num_ops; $i++) {
            $numbers[] = random_int(2, 15);
        }
        $expr .= $numbers[0];
        for ($i = 1; $i <= $num_ops; $i++) {
            $op = $ops[random_int(0, count($ops)-1)];
            // Aleatoriamente usar paréntesis
            if ($i == 1 && random_int(0,1)) {
                $expr = '(' . $expr . ' ' . $op . ' ' . $numbers[$i] . ')';
            } else {
                $expr .= ' ' . $op . ' ' . $numbers[$i];
            }
        }
        // Evaluar la expresión de forma segura
        $safe_expr = preg_replace('/[^0-9\+\-\*\(\) ]/', '', $expr);
        try {
            // eval solo de números y operaciones
            $answer = eval('return ' . $safe_expr . ';');
        } catch (Throwable $e) {
            $answer = null;
        }
        $question = '¿Cuánto es ' . $expr . '?';
        $_SESSION['captcha'][$form_key] = [
            'question' => $question,
            'answer' => $answer,
        ];
        return $_SESSION['captcha'][$form_key];
    } else {
        // Captcha simple
        $first_number = random_int(1, 9);
        $second_number = random_int(1, 9);
        $question = sprintf('¿Cuánto es %d + %d?', $first_number, $second_number);
        $_SESSION['captcha'][$form_key] = [
            'question' => $question,
            'answer' => $first_number + $second_number,
        ];
        return $_SESSION['captcha'][$form_key];
    }
}

/**
 * Retrieves the captcha question for a form. Generates a new one if it does not exist yet.
 *
 * @param string $form_key Identifier of the form the captcha belongs to.
 */
function captcha_get_question(string $form_key): string
{
    captcha_ensure_storage();

    if (!isset($_SESSION['captcha'][$form_key]['question'])) {
        captcha_refresh($form_key);
    }

    return (string) $_SESSION['captcha'][$form_key]['question'];
}

/**
 * Validates the provided captcha answer for the given form key.
 * Always generates a new challenge after checking the current answer.
 *
 * @param string     $form_key    Identifier of the form the captcha belongs to.
 * @param null|mixed $user_answer Answer provided by the user.
 */
function captcha_validate(string $form_key, $user_answer): bool
{
    captcha_ensure_storage();

    if (!isset($_SESSION['captcha'][$form_key])) {
        captcha_refresh($form_key);
        return false;
    }

    $expected_answer = (int) ($_SESSION['captcha'][$form_key]['answer'] ?? 0);
    $answer = is_string($user_answer) ? trim($user_answer) : '';

    $is_valid = $answer !== ''
        && preg_match('/^-?\d+$/', $answer) === 1
        && (int) $answer === $expected_answer;

    captcha_refresh($form_key);

    return $is_valid;
}
