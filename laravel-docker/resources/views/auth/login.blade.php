<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PraceAR - Acceso admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ url('/img/favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/img/apple-touch-icon-180x180.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('/img/apple-touch-icon-152x152.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="{{ url('/css/panda_login.css') }}">
    <link rel="stylesheet" href="{{ url('/css/darkmode_login.css') }}">
    <style>
        .required::after {
            content: " *";
            color: red;
        }

        .note {
            color: red;
            text-align: center;
        }

        body.login-page {
            min-height: 100vh;
            margin: 0;
            width: 100%;
            max-width: none;
            padding: 0;
            font-family: "Inter", sans-serif !important;
            background: radial-gradient(circle at 20% 20%, #f0f8ff 0%, #e9eef7 40%, #dde5f2 100%);
            color: #333;
        }

        .login-shell {
            width: min(980px, 94vw);
            margin: 0 auto;
            padding: 1.5rem 1rem 2.5rem;
            display: grid;
            gap: 1rem;
        }

        .login-grid {
            display: grid;
            grid-template-columns: minmax(300px, 1fr) minmax(320px, 420px);
            align-items: center;
            gap: 1.5rem;
            width: 100%;
            justify-content: center;
            justify-items: center;
        }

        .login-card {
            width: min(100%, 560px);
            margin: 0;
            border-radius: 18px;
            border: 1px solid rgba(45, 64, 89, 0.18);
            box-shadow: 0 16px 36px rgba(34, 51, 72, 0.12);
            backdrop-filter: blur(1px);
            background: rgba(255, 255, 255, 0.96);
            padding: 1.5rem;
        }

        .login-card h2 {
            margin-bottom: 0.35rem;
            text-align: center;
        }

        .login-intro {
            text-align: center;
            margin-bottom: 1rem;
            color: #475569;
        }

        .login-messages {
            margin-top: 1rem;
            display: grid;
            gap: 0.4rem;
            text-align: center;
        }

        .error-list {
            display: grid;
            gap: 0.35rem;
            color: #be123c;
            margin-bottom: 0.8rem;
            text-align: center;
        }

        @media (max-width: 820px) {
            .login-grid {
                grid-template-columns: 1fr;
            }

            .login-card {
                order: 2;
            }

            .panda-login-panda {
                order: 1;
            }
        }
    </style>
    <script type="module" src="{{ url('/js/login.js') }}" defer></script>
</head>

<body class="login-page">
    <main class="login-shell">
        <div class="login-grid">
            <article class="login-card">
                <h2 id="form-title">Inicio de sesión</h2>
                <p class="login-intro">Introduce tus credenciales para gestionar puestos, mapas, traducciones y seguridad.</p>

                @if ($errors->any())
                <div class="error-list" role="alert">
                    @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ url()->current() }}" id="formulario" aria-labelledby="form-title" novalidate>
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
                        <p id="captcha-question" style="margin-bottom: .5rem;">{{ $captchaQuestion }}</p>
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
    <script src="{{ url('/js/helpers/dark_mode.js') }}" defer></script>
</body>

</html>