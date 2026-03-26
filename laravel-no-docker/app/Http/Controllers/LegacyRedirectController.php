<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyRedirectController extends Controller
{
    public function home(): RedirectResponse
    {
        return $this->redirectToLegacy((string) config('transition.legacy_paths.home'));
    }

    public function login(): RedirectResponse
    {
        return $this->redirectToLegacy((string) config('transition.legacy_paths.login'));
    }

    public function admin(Request $request, ?string $path = null): RedirectResponse
    {
        $normalizedPath = trim((string) $path, '/');

        if ($normalizedPath !== '' && Str::contains($normalizedPath, ['..', '\\'])) {
            abort(404);
        }

        if ($normalizedPath === '') {
            return $this->redirectToLegacy((string) config('transition.legacy_paths.admin'));
        }

        return $this->redirectToLegacy('admin/' . $normalizedPath);
    }

    private function redirectToLegacy(string $path): RedirectResponse
    {
        $baseUrl = rtrim((string) config('transition.legacy_base_url'), '/');
        $normalizedPath = '/' . ltrim($path, '/');

        return redirect()->away($baseUrl . $normalizedPath);
    }
}