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

    $first_number = random_int(1, 9);
    $second_number = random_int(1, 9);
    $question = sprintf('¿Cuánto es %d + %d?', $first_number, $second_number);

    $_SESSION['captcha'][$form_key] = [
        'question' => $question,
        'answer' => $first_number + $second_number,
    ];

    return $_SESSION['captcha'][$form_key];
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
