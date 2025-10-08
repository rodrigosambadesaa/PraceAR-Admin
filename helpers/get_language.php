<?php
declare(strict_types=1);

function get_language(): string
{
    $language = $_REQUEST['lang'] ?? 'gl';

    return is_string($language) && $language !== '' ? $language : 'gl';
}

