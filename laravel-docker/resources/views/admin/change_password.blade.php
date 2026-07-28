@extends('layouts.admin')

@section('title', 'Admin - PraceAR - Cambiar contraseña')
@section('activePage', 'change_password')
@section('bodyClass', 'admin-change-password')

@php
$passwordMinLength = \App\Security\PasswordPolicy::MIN_LENGTH;
$passwordMaxLength = \App\Security\PasswordPolicy::MAX_LENGTH;
@endphp

@push('styles')
<link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/admin/css/change_password.css' }}">
<link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/admin/css/change_password_redesign.css' }}">
@endpush

@section('content')
<main class="admin-page-main admin-password-main">
    <section class="admin-page-intro" aria-labelledby="admin-password-title">
        <div class="admin-page-intro__panel">
            <p class="admin-eyebrow">Seguridad de acceso</p>
            <h2 id="admin-password-title" class="admin-page-title">Cambiar contraseña</h2>
            <p class="admin-page-lead">Refuerza la cuenta administrativa con una clave robusta y, si quieres, genera una contraseña segura desde el propio panel.</p>
            <div class="admin-metadata">
                <span class="admin-chip">Usuario {{ $username }}</span>
                <span class="admin-chip">Mínimo 16 caracteres</span>
                <span class="admin-chip">Gestión local</span>
            </div>
        </div>
        <aside class="admin-page-intro__aside">
            <p class="admin-eyebrow">Buenas prácticas</p>
            <h3>Qué conviene hacer</h3>
            <ul class="admin-guidance-list">
                <li>Usar un gestor de contraseñas y guardar una clave única.</li>
                <li>No reutilizar combinaciones antiguas o relacionadas con el usuario.</li>
                <li>Copiar la contraseña generada solo si la vas a registrar de inmediato.</li>
            </ul>
        </aside>
    </section>

    @if ($errors->any() || session('status'))
    <div class="admin-feedback-stack">
        @foreach ($errors->all() as $error)
        <div class="admin-alert admin-alert--error">
            <p>{{ $error }}</p>
        </div>
        @endforeach

        @if (session('status'))
        <div class="admin-alert admin-alert--success success-message">
            <p>{{ session('status') }}</p>
        </div>
        @endif
    </div>
    @endif

    <div class="password-layout">
        <section class="admin-section-card password-form-card">
            <div class="admin-section-card__header">
                <div>
                    <p class="admin-eyebrow">Formulario</p>
                    <h3 id="formulario-titulo">Actualiza tus credenciales</h3>
                    <p>La validación y el generador siguen funcionando igual; solo cambia la presentación.</p>
                </div>
            </div>

            <form method="POST" action="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => 'change_password', 'lang' => $currentLang]) }}" id="formulario-cambio-contrasena" aria-labelledby="formulario-titulo">
                @csrf
                <input type="hidden" name="csrf" value="{{ csrf_token() }}">

                <div class="password-form-group">
                    <label for="nombre-usuario">Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" id="nombre-usuario" value="{{ $username }}" disabled aria-disabled="true">
                </div>

                <div class="password-form-group">
                    <label for="old-password">Contraseña actual <span class="admin-required" aria-hidden="true">*</span></label>
                    <input type="password" name="old_password" id="old-password" required maxlength="{{ $passwordMaxLength }}" aria-describedby="password-help">
                    <p id="password-help" class="sr-only">Introduce tu contraseña actual.</p>
                </div>

                <div class="password-form-group password-generator-group">
                    <label for="new-password">Nueva contraseña <span class="admin-required" aria-hidden="true">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" name="new_password" id="new-password" required maxlength="{{ $passwordMaxLength }}"
                            aria-describedby="new-password-help">
                        <button type="button" id="toggle-password-generator" class="password-generator-toggle" aria-expanded="false"
                            aria-controls="password-generator-panel">
                            Generar contraseña segura
                        </button>
                    </div>
                    <p id="new-password-help" class="sr-only">Introduce una nueva contraseña que cumpla con los requisitos.</p>

                    <section id="password-generator-panel" class="password-generator-panel" aria-live="polite" hidden>
                        <h2 id="password-generator-heading">Generador de contraseñas</h2>
                        <div class="password-generator-length" role="group" aria-labelledby="password-generator-heading">
                            <label for="password-length-number">Longitud de la contraseña ({{ $passwordMinLength }}-{{ $passwordMaxLength }})</label>
                            <input type="number" id="password-length-number" min="{{ $passwordMinLength }}" max="{{ $passwordMaxLength }}" value="{{ $passwordMinLength }}" aria-describedby="password-length-help">
                            <input type="range" id="password-length-range" min="{{ $passwordMinLength }}" max="{{ $passwordMaxLength }}" value="{{ $passwordMinLength }}" aria-describedby="password-length-help">
                            <output id="password-length-output" class="password-length-output">{{ $passwordMinLength }}</output>
                        </div>
                        <p id="password-length-help" class="sr-only">Selecciona una longitud entre {{ $passwordMinLength }} y {{ $passwordMaxLength }} caracteres.</p>
                        <p id="password-length-resistance" class="password-generator-resistance" aria-live="polite"></p>
                        <div class="password-generator-actions">
                            <button type="button" class="password-generator-button" id="generate-password-button">Generar otra contraseña</button>
                            <span id="password-generator-feedback" class="password-generator-feedback admin-info-text" role="status" aria-live="polite"></span>
                        </div>
                        <div id="generated-password-container" class="password-generator-result" hidden>
                            <div class="password-generator-password">
                                <span class="password-generator-password-label">Contraseña generada y aplicada al formulario</span>
                                <div id="generated-password-value" class="password-generator-password-value" aria-live="polite"></div>
                            </div>
                            <div class="password-generator-actions">
                                <button type="button" class="password-generator-button password-generator-copy" id="copy-generated-password">Copiar contraseña</button>
                                <span id="password-copy-feedback" class="password-generator-feedback" role="status" aria-live="polite"></span>
                            </div>
                            <div class="password-generator-stats" aria-live="polite">
                                <div><span class="password-generator-stat-label">Mayúsculas</span><span id="password-stat-uppercase" class="password-generator-stat-value">0</span></div>
                                <div><span class="password-generator-stat-label">Minúsculas</span><span id="password-stat-lowercase" class="password-generator-stat-value">0</span></div>
                                <div><span class="password-generator-stat-label">Dígitos</span><span id="password-stat-digits" class="password-generator-stat-value">0</span></div>
                                <div><span class="password-generator-stat-label">Caracteres especiales</span><span id="password-stat-special" class="password-generator-stat-value">0</span></div>
                                <div><span class="password-generator-stat-label">Entropía estimada</span><span id="password-stat-entropy" class="password-generator-stat-value">-</span></div>
                            </div>
                            <div class="password-generator-stats password-generator-stats--detail">
                                <div class="password-generator-explanation">
                                    <span class="password-generator-stat-label">Resistencia estimada</span>
                                    <span id="password-stat-hash" class="password-generator-stat-value password-generator-resistance">-</span>
                                    <div class="password-generator-caption">
                                        Estimación cualitativa basada en la entropía. Considera usar un gestor de contraseñas.
                                    </div>
                                </div>
                            </div>
                            <section class="password-attack-estimates" aria-labelledby="password-attack-estimates-heading">
                                <h3 id="password-attack-estimates-heading">Tiempo medio estimado por tipo de ataque</h3>
                                <p id="password-attack-search-space" class="password-generator-caption"></p>
                                <div class="password-attack-estimates__table-wrap">
                                    <table class="password-attack-estimates__table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Escenario</th>
                                                <th scope="col">Supuesto</th>
                                                <th scope="col">Velocidad</th>
                                                <th scope="col">Tiempo medio</th>
                                            </tr>
                                        </thead>
                                        <tbody id="password-attack-estimates-body"></tbody>
                                    </table>
                                </div>
                                <p class="password-generator-caption">
                                    Cálculo de fuerza bruta sobre la mitad del espacio de búsqueda, realizado con enteros arbitrariamente grandes y mostrado en notación científica. No contempla phishing, malware, reutilización ni filtraciones de la contraseña.
                                </p>
                            </section>
                        </div>
                    </section>
                </div>

                <div class="password-form-group">
                    <label for="confirm-password">Confirmar nueva contraseña <span class="admin-required" aria-hidden="true">*</span></label>
                    <input type="password" name="confirm_password" id="confirm-password" required maxlength="{{ $passwordMaxLength }}" aria-describedby="confirm-password-help">
                    <p id="confirm-password-help" class="sr-only">Introduce nuevamente la nueva contraseña para confirmarla.</p>
                </div>

                <div class="password-form-group password-submit-group">
                    <input type="submit" value="Cambiar contraseña" aria-label="Cambiar contraseña">
                </div>
            </form>
        </section>

        <aside class="admin-section-card password-side-card">
            <div id="password-requirements" aria-live="polite">
                <span>Requisitos de la nueva contraseña</span>
                <ul>
                    <li>Longitud entre {{ $passwordMinLength }} y {{ $passwordMaxLength }} caracteres.</li>
                    <li>Una letra mayúscula.</li>
                    <li>Una letra minúscula.</li>
                    <li>Un número.</li>
                    <li>Tres caracteres especiales distintos.</li>
                    <li>Sin espacios al principio o al final.</li>
                    <li>Sin secuencias numéricas inseguras.</li>
                    <li>Sin secuencias alfabéticas inseguras.</li>
                    <li>Sin secuencias de caracteres especiales inseguras.</li>
                </ul>
            </div>

            <p class="admin-note">Los campos marcados con <span class="admin-required" aria-hidden="true">*</span> son obligatorios.</p>
            <span id="help-text" class="admin-info-text">Usa un gestor de contraseñas y genera claves únicas de 16 o más caracteres.</span>
        </aside>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ rtrim($baseUrl, '/') . '/admin/third-party/big-integer/BigInteger.min.js' }}"></script>
<script type="module" src="{{ rtrim($baseUrl, '/') . '/admin/js/check_password_requirements.js' }}"></script>
<script type="module" src="{{ rtrim($baseUrl, '/') . '/admin/js/change_password.js' }}"></script>
@endpush
