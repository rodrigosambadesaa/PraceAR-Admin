<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - PraceAR')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ url('/img/favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/img/apple-touch-icon-180x180.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('/img/apple-touch-icon-152x152.png') }}">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="{{ url('/admin/css/theme.css') }}">
    <link rel="stylesheet" href="{{ url('/admin/css/header.css') }}">
    <link rel="stylesheet" href="{{ url('/admin/css/admin_shell.css') }}">
    @stack('styles')
</head>

@php
$rootUrl = url('/');
$activePage = trim($__env->yieldContent('activePage')) !== '' ? trim($__env->yieldContent('activePage')) : 'index';
$currentQuery = request()->query();
$languageSelectorPlacement = trim($__env->yieldContent('languageSelectorPlacement')) !== '' ? trim($__env->yieldContent('languageSelectorPlacement')) : 'header';
$pageLabels = [
'index' => 'Puestos',
'edit' => 'Edición',
'market_sections' => 'Naves',
'change_password' => 'Seguridad',
'language' => 'Traducciones',
];
$pageDescriptions = [
'index' => 'Gestiona el catálogo completo del mercado, busca puestos y edita datos en línea.',
'edit' => 'Actualiza datos comerciales, fotografía y ubicación del puesto seleccionado.',
'market_sections' => 'Consulta los mapas operativos y revisa la distribución visual del recinto.',
'change_password' => 'Refuerza el acceso administrativo con credenciales robustas y únicas.',
'language' => 'Mantén al día los contenidos multilingües visibles para las visitas.',
];
$currentSection = $pageLabels[$activePage] ?? 'Panel';
$currentSectionDescription = $pageDescriptions[$activePage] ?? 'Gestiona el contenido y la configuración del panel administrativo.';
@endphp

<body class="admin-page @yield('bodyClass', '') {{ request()->cookie('dark_mode') === 'true' ? 'dark-mode' : '' }}">
    <script>
        (function() {
            var storageKey = "dark-mode";
            var legacyStorageKey = "darkmode";
            var cookieKey = "dark_mode";

            function escapeRegExp(value) {
                return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
            }

            function getCookieValue(name) {
                var match = document.cookie.match(new RegExp("(?:^|; )" + escapeRegExp(name) + "=([^;]*)"));
                return match ? decodeURIComponent(match[1]) : null;
            }

            function getStorageValue(key) {
                try {
                    return window.localStorage.getItem(key);
                } catch (error) {
                    return null;
                }
            }

            function setStorageValue(key, value) {
                try {
                    window.localStorage.setItem(key, value);
                } catch (error) {}
            }

            function removeStorageValue(key) {
                try {
                    window.localStorage.removeItem(key);
                } catch (error) {}
            }

            function persistPreference(value) {
                var serialized = String(value);
                setStorageValue(storageKey, serialized);
                removeStorageValue(legacyStorageKey);
                document.cookie = cookieKey + "=" + encodeURIComponent(serialized) + "; Path=/; Max-Age=31536000; SameSite=Lax";
            }

            function readStoredPreference() {
                var cookieValue = getCookieValue(cookieKey);
                if (cookieValue === "true") {
                    setStorageValue(storageKey, "true");
                    removeStorageValue(legacyStorageKey);
                    return true;
                }
                if (cookieValue === "false") {
                    setStorageValue(storageKey, "false");
                    removeStorageValue(legacyStorageKey);
                    return false;
                }

                var storageValue = getStorageValue(storageKey);
                if (storageValue === null) {
                    storageValue = getStorageValue(legacyStorageKey);
                }

                if (storageValue === "true") {
                    return true;
                }
                if (storageValue === "false") {
                    return false;
                }

                return null;
            }

            var isDark = readStoredPreference();

            if (isDark === null) {
                isDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
                persistPreference(isDark);
            }

            document.documentElement.classList.toggle("dark-mode", isDark === true);
            document.documentElement.style.colorScheme = isDark === true ? "dark" : "light";
            document.body.classList.toggle("dark-mode", isDark === true);
        })();
    </script>
    <header class="admin-header" role="banner">
        <div class="admin-header__top">
            <div class="admin-header__title-row">
                <div class="admin-header__brand">
                    <p class="admin-header__eyebrow">Panel de administración</p>
                    <div class="admin-header__headline">
                        <h1 id="cabecera_pagina_edicion" tabindex="0">PraceAR Admin</h1>
                        <span class="admin-header__section-badge">{{ $currentSection }}</span>
                    </div>
                    <p class="admin-header__summary">{{ $currentSectionDescription }}</p>
                </div>
                <div class="admin-header__tools">
                    <strong class="admin-header__language">
                        <span>Idioma actual</span>
                        <img class="language-flag current-language-flag" width="15" height="15"
                            src="{{ url('/img/flags/' . $currentLang . '.png') }}" alt="{{ $currentLang }}" tabindex="0">
                        <span>{{ strtoupper($currentLang) }}</span>
                    </strong>
                    <div class="admin-header__darkmode">
                        <button id="toggle-darkmode" aria-label="Cambiar modo oscuro"
                            type="button">
                            <span id="darkmode-icon" title="Cambiar modo oscuro">🌙</span>
                        </button>
                    </div>
                </div>
            </div>

            @if ($languageSelectorPlacement !== 'content')
            <div class="admin-header__selector">
                @include('admin.partials.language_selector', [
                'activePage' => $activePage,
                'currentLang' => $currentLang,
                'languages' => $languages,
                'rootUrl' => $rootUrl,
                ])
            </div>
            @endif
        </div>

        <div id="cabecera-menu-navegacion" class="admin-header__menu">
            <nav class="main-nav" role="navigation" aria-label="Menú principal">
                <a href="{{ $rootUrl . '?' . http_build_query(['lang' => $currentLang]) }}"
                    class="nav-link {{ $activePage === 'index' ? 'is-active' : '' }}"
                    aria-label="Ir a Inicio"
                    @if ($activePage==='index' ) aria-current="page" @endif>Inicio</a>
                <a href="{{ $rootUrl . '?' . http_build_query(['page' => 'market_sections', 'lang' => $currentLang]) }}"
                    class="nav-link {{ $activePage === 'market_sections' ? 'is-active' : '' }}"
                    aria-label="Ir a Naves"
                    @if ($activePage==='market_sections' ) aria-current="page" @endif>Naves</a>
                <a href="{{ $rootUrl . '?' . http_build_query(['page' => 'change_password', 'lang' => $currentLang]) }}"
                    class="nav-link {{ $activePage === 'change_password' ? 'is-active' : '' }}"
                    aria-label="Cambiar contraseña"
                    @if ($activePage==='change_password' ) aria-current="page" @endif>Cambiar contraseña</a>
                <a href="{{ $rootUrl . '?' . http_build_query(['page' => 'logout', 'lang' => $currentLang]) }}" class="nav-link enlace_cierre_sesion nav-link--logout" aria-label="Cerrar sesión">
                    <img src="{{ url('/img/logout_icon.png') }}" alt="Cerrar sesión" title="Cerrar sesión">
                    <span>Salir</span>
                </a>
            </nav>
        </div>
    </header>

    <div class="admin-shell">
        @yield('content')
    </div>

    <script>
        window.BASE_URL = "{{ $baseUrl }}";
    </script>
    @stack('scripts')
    <script src="{{ url('/js/helpers/dark_mode.js') }}"></script>
</body>

</html>