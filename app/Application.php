<?php

declare(strict_types=1);

namespace App;

use App\Controller\LegacyAdminController;
use App\Controller\LegacyAuthController;
use App\Core\Bootstrap;
use App\Core\Router;
use App\Http\Request;

final class Application
{
    public function __construct(private readonly string $projectRoot) {}

    public function run(): void
    {
        Bootstrap::initialize($this->projectRoot);

        $request = Request::fromGlobals();

        if (!isset($_SESSION['login'])) {
            (new LegacyAuthController($this->projectRoot))->login();
            return;
        }

        $adminController = new LegacyAdminController();
        $router = new Router();

        $allowedRoutes = ['index', 'market_sections', 'change_password', 'edit', 'language', 'logout'];
        foreach ($allowedRoutes as $route) {
            $router->add($route, static function (Request $r) use ($adminController): void {
                $adminController->render($r);
            });
        }

        $router->setDefault('index');
        $router->dispatch($request->page(), $request);
    }
}
