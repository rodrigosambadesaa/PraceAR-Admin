<?php

namespace App\Http\Controllers;

use App\Support\PracearSupport;
use Illuminate\Http\Request;

class RootController extends Controller
{
    public function __construct(
        private readonly AuthController $authController,
        private readonly AdminController $adminController,
    ) {
    }

    public function show(Request $request)
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return $this->authController->show($request);
        }

        $page = $request->query('page');

        if ($page === 'logout') {
            return $this->adminController->logout($request);
        }

        if ($page === 'market_sections') {
            return $this->adminController->marketSections($request);
        }

        if ($page === 'change_password') {
            return $this->adminController->changePasswordForm($request);
        }

        if ($page === 'edit') {
            return $this->adminController->editForm($request);
        }

        if ($page === 'language') {
            return $this->adminController->translationsForm($request);
        }

        return $this->adminController->index($request);
    }

    public function submit(Request $request)
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return $this->authController->login($request);
        }

        $page = $request->input('page', $request->query('page'));

        if ($page === 'change_password') {
            return $this->adminController->changePasswordUpdate($request);
        }

        if ($page === 'edit') {
            return $this->adminController->editUpdate($request);
        }

        if ($page === 'language') {
            return $this->adminController->translationsUpdate($request);
        }

        return $this->adminController->search($request);
    }
}