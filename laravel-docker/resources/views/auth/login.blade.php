<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PraceAR - Acceso admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ rtrim($baseUrl, '/') . '/img/favicon.png' }}" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ rtrim($baseUrl, '/') . '/img/apple-touch-icon-180x180.png' }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ rtrim($baseUrl, '/') . '/img/apple-touch-icon-152x152.png' }}">
    <link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/css/base.css' }}">
    <link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/css/panda_login.css' }}">
    <link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/css/darkmode_login.css' }}">
    <link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/css/login_shell.css' }}">
    <script type="module" src="{{ rtrim($baseUrl, '/') . '/js/login.js' }}" defer></script>
</head>

<body class="login-page">
    <main class="login-shell">
        <div class="login-grid">
            <article class="login-card">
                <div class="login-card-header">
                    <h2 id="form-title">Inicio de sesión</h2>
                    <button id="toggle-darkmode" class="login-theme-toggle" type="button" aria-label="Cambiar modo oscuro" aria-pressed="false">
                        <span id="darkmode-icon" title="Cambiar modo oscuro">🌙</span>
                    </button>
                </div>
                <p class="login-intro">Introduce tus credenciales para gestionar puestos, mapas, traducciones y seguridad.</p>

                @if ($errors->any())
                <div class="error-list" role="alert">
                    @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="" id="formulario" aria-labelledby="form-title" novalidate>
                    @csrf
                    <input type="hidden" name="lang" value="{{ $currentLang }}">

                    <div id="form-group">
                        <label for="login" class="required"><strong>Usuario:</strong></label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" autocomplete="username" required>
                        <small id="login-help">Ingrese su nombre de usuario registrado.</small>
                    </div>

                    <div id="form-group">
                        <label for="password" class="required"><strong>Contraseña:</strong></label>
                        <input type="password" id="password" name="password" autocomplete="current-password" required>
                        <small id="password-help">Ingrese su contraseña. Debe tener entre 16 y 1024 caracteres.</small>
                    </div>

                    <div id="form-group">
                        <label for="captcha" class="required"><strong>Verificación humana:</strong></label>
                        <p id="captcha-question" class="captcha-question">{{ $captchaQuestion }}</p>
                        <input type="text" id="captcha" name="captcha_answer" inputmode="numeric" required>
                        <small id="captcha-help">Responda con el resultado numérico de la pregunta.</small>
                    </div>

                    <div id="form-group">
                        <input type="submit" value="Iniciar sesión" aria-label="Iniciar sesión">
                    </div>
                </form>

                <div class="login-messages">
                    <p class="note" role="alert" aria-live="polite">Los campos marcados con * son obligatorios</p>
                </div>
            </article>

            <div class="panda-login-panda" aria-hidden="true">
                <div class="backg">
                    <div class="panda">
                        <div class="earl"></div>
                        <div class="earr"></div>
                        <div class="face">
                            <div class="blshl"></div>
                            <div class="blshr"></div>
                            <div class="eyel">
                                <div class="eyeball1"></div>
                            </div>
                            <div class="eyer">
                                <div class="eyeball2"></div>
                            </div>
                            <div class="nose">
                                <div class="line"></div>
                            </div>
                            <div class="mouth">
                                <div class="m">
                                    <div class="m1"></div>
                                </div>
                                <div class="mm">
                                    <div class="m1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pawl">
                    <div class="p1">
                        <div class="p2"></div>
                        <div class="p3"></div>
                        <div class="p4"></div>
                    </div>
                </div>
                <div class="pawr">
                    <div class="p1">
                        <div class="p2"></div>
                        <div class="p3"></div>
                        <div class="p4"></div>
                    </div>
                </div>
                <div class="handl"></div>
                <div class="handr"></div>
            </div>
        </div>
    </main>
    <script src="{{ rtrim($baseUrl, '/') . '/js/helpers/dark_mode.js' }}" defer></script>
</body>

</html>
