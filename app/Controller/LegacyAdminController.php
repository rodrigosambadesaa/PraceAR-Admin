<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;

final class LegacyAdminController
{
    /** @var array<string, string> */
    private array $pageToFile = [
        'index' => 'index.php',
        'market_sections' => 'market_sections.php',
        'change_password' => 'change_password.php',
        'edit' => 'edit.php',
        'language' => 'edit_translations.php',
        'logout' => 'logout.php',
    ];

    public function render(Request $request): void
    {
        $page = $request->page();
        $fileName = $this->pageToFile[$page] ?? $this->pageToFile['index'];
        $conexion = $GLOBALS['conexion'] ?? null;

        if (in_array($page, ['index', 'market_sections', 'change_password'], true)) {
            $_GET['page_number'] = $request->currentPageNumber();
        }

        require_once ADMIN . $fileName;
    }
}
