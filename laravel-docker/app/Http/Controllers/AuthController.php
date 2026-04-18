<?php

namespace App\Http\Controllers;

use App\Support\PracearSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (PracearSupport::isAuthenticated($request)) {
            return redirect()->to($this->rootUrl(['lang' => PracearSupport::language($request->query('lang'))]));
        }

        $captcha = $this->ensureCaptcha($request);

        return view('auth.login', [
            'baseUrl' => PracearSupport::baseUrl(),
            'captchaQuestion' => $captcha['question'],
            'currentLang' => PracearSupport::language($request->query('lang')),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $genericMessage = (string) (PracearSupport::securityConfig()['auth']['generic_login_error_message'] ?? 'Credenciales inválidas.');
        $rawLogin = (string) $request->input('login', '');
        $password = (string) $request->input('password', '');
        $loginKey = strtolower(trim($rawLogin));
        $ipKey = 'pracear:login:ip:' . sha1((string) $request->ip());
        $accountKey = 'pracear:login:account:' . sha1($loginKey !== '' ? $loginKey : 'unknown');
        $rateLimitConfig = (array) (PracearSupport::securityConfig()['rate_limit'] ?? []);
        $ipLimit = (int) (($rateLimitConfig['ip']['max_attempts'] ?? 5));
        $ipDecay = (int) (($rateLimitConfig['ip']['interval_seconds'] ?? 60));
        $accountLimit = (int) (($rateLimitConfig['account']['max_attempts'] ?? 10));
        $accountDecay = (int) (($rateLimitConfig['account']['interval_seconds'] ?? 600));

        if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            return $this->rateLimitedResponse($request, $ipKey);
        }

        if ($loginKey !== '' && RateLimiter::tooManyAttempts($accountKey, $accountLimit)) {
            return $this->rateLimitedResponse($request, $accountKey);
        }

        try {
            $login = PracearSupport::validateLoginName($rawLogin);
        } catch (Throwable) {
            RateLimiter::hit($ipKey, $ipDecay);
            if ($loginKey !== '') {
                RateLimiter::hit($accountKey, $accountDecay);
            }

            $this->refreshCaptcha($request);

            return redirect()->to($this->loginUrl($request))
                ->withInput($request->except('password', 'captcha_answer'))
                ->withErrors(['login' => $genericMessage]);
        }

        if (!$this->captchaIsValid($request)) {
            RateLimiter::hit($ipKey, $ipDecay);
            RateLimiter::hit($accountKey, $accountDecay);

            return redirect()->to($this->loginUrl($request))
                ->withInput($request->except('password', 'captcha_answer'))
                ->withErrors(['captcha_answer' => 'La verificación captcha no es correcta.']);
        }

        try {
            $user = DB::table('usuarios')->where('login', $login)->first();
        } catch (Throwable $exception) {
            Log::error('No se pudo completar el login por un error de base de datos.', [
                'login' => $login,
                'message' => $exception->getMessage(),
            ]);

            $errorMessage = 'No se ha podido verificar el acceso en este momento. Inténtelo de nuevo en unos instantes.';
            if ((bool) config('app.debug')) {
                $errorMessage .= ' [' . $exception->getMessage() . ']';
            }

            RateLimiter::hit($ipKey, $ipDecay);
            RateLimiter::hit($accountKey, $accountDecay);

            return redirect()->to($this->loginUrl($request))
                ->withInput($request->except('password', 'captcha_answer'))
                ->withErrors(['login' => $errorMessage]);
        }

        if (!$user) {
            RateLimiter::hit($ipKey, $ipDecay);
            RateLimiter::hit($accountKey, $accountDecay);

            return redirect()->to($this->loginUrl($request))
                ->withInput($request->except('password', 'captcha_answer'))
                ->withErrors(['login' => $genericMessage]);
        }

        $matchedPepper = PracearSupport::matchingPepper($password, (string) $user->password);

        if ($matchedPepper === null) {
            RateLimiter::hit($ipKey, $ipDecay);
            RateLimiter::hit($accountKey, $accountDecay);

            return redirect()->to($this->loginUrl($request))
                ->withInput($request->except('password', 'captcha_answer'))
                ->withErrors(['login' => $genericMessage]);
        }

        $currentPepper = PracearSupport::currentPepper();
        if ($matchedPepper !== $currentPepper || PracearSupport::passwordNeedsRehash((string) $user->password)) {
            DB::table('usuarios')
                ->where('id', $user->id)
                ->update(['password' => PracearSupport::hashPassword($password, $currentPepper)]);
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($accountKey);
        $this->refreshCaptcha($request);

        $request->session()->regenerate();
        $request->session()->put([
            'pracear.user_id' => (int) $user->id,
            'pracear.username' => (string) $user->login,
        ]);

        try {
            DB::table('accesos')->insert([
                'id_usuario' => (int) $user->id,
                'ip' => (string) $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'fecha' => now(),
                'tipo' => 'acceso',
            ]);
        } catch (Throwable $exception) {
            Log::warning('No se pudo registrar el acceso en la tabla de auditoria.', [
                'user_id' => (int) $user->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return redirect()->to($this->rootUrl(['lang' => PracearSupport::language($request->input('lang'))]));
    }

    private function rateLimitedResponse(Request $request, string $key): RedirectResponse
    {
        $retryAfter = RateLimiter::availableIn($key);

        return redirect()->to($this->loginUrl($request))
            ->withInput($request->except('password', 'captcha_answer'))
            ->withErrors(['login' => 'Se superó el límite de intentos. Espere ' . max(1, $retryAfter) . ' segundos antes de volver a intentarlo.']);
    }

    private function captchaIsValid(Request $request): bool
    {
        $expectedAnswer = (int) $request->session()->get('pracear.login_captcha.answer', 0);
        $providedAnswer = trim((string) $request->input('captcha_answer', ''));

        $isValid = $providedAnswer !== '' && preg_match('/^-?\d+$/', $providedAnswer) === 1 && (int) $providedAnswer === $expectedAnswer;

        $this->refreshCaptcha($request);

        return $isValid;
    }

    private function ensureCaptcha(Request $request): array
    {
        $captcha = $request->session()->get('pracear.login_captcha');

        if (is_array($captcha) && isset($captcha['question'], $captcha['answer'])) {
            return $captcha;
        }

        return $this->refreshCaptcha($request);
    }

    private function refreshCaptcha(Request $request): array
    {
        $first = random_int(1, 9);
        $second = random_int(1, 9);
        $captcha = [
            'question' => sprintf('¿Cuánto es %d + %d?', $first, $second),
            'answer' => $first + $second,
        ];

        $request->session()->put('pracear.login_captcha', $captcha);

        return $captcha;
    }

    private function rootUrl(array $params = []): string
    {
        $baseUrl = rtrim(PracearSupport::baseUrl(), '/') . '/';

        if ($params === []) {
            return $baseUrl;
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    private function loginUrl(Request $request): string
    {
        $language = PracearSupport::language((string) $request->input('lang', (string) $request->query('lang')));

        return rtrim(PracearSupport::baseUrl(), '/') . '/login?lang=' . rawurlencode($language);
    }
}
