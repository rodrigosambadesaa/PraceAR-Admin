@extends('layouts.admin')

@section('title', 'Admin - PraceAR - Editar traducción')
@section('activePage', 'language')
@section('bodyClass', 'admin-edit-translations')

@push('styles')
<link rel="stylesheet" href="{{ url('/admin/css/translations.css') }}">
@endpush

@section('content')
<main class="admin-page-main admin-translations-main">
    <section class="admin-page-intro" aria-labelledby="translation-page-title">
        <div class="admin-page-intro__panel">
            <p class="admin-eyebrow">Contenido multilingüe</p>
            <h2 id="translation-page-title" class="admin-page-title">Editar traducción del puesto</h2>
            <p class="admin-page-lead">Ajusta el tipo y la descripción visibles para el idioma seleccionado sin modificar el resto de la ficha.</p>
            <div class="admin-metadata">
                <span class="admin-chip">{{ $stallName ?: 'Nombre no disponible' }}</span>
                <span class="admin-chip">Idioma {{ strtoupper($currentLang) }}</span>
                <span class="admin-chip">ID {{ $stallId }}</span>
            </div>
        </div>
        <aside class="admin-page-intro__aside">
            <p class="admin-eyebrow">Contexto</p>
            <h3>Qué revisar</h3>
            <ul class="admin-guidance-list">
                <li>Que el tipo conserve el mismo significado comercial en todos los idiomas.</li>
                <li>Que la descripción siga siendo breve y útil para visitantes.</li>
                <li>Que el texto no exceda el límite operativo del formulario.</li>
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
        <div class="admin-alert admin-alert--success">
            <p>{{ session('status') }}</p>
        </div>
        @endif
    </div>
    @endif

    <section class="admin-section-card translation-card">
        <div class="translation-card__header">
            <div>
                <p class="admin-eyebrow">Formulario</p>
                <h3 id="formulario-titulo">Versión en {{ strtoupper($currentLang) }}</h3>
                <p>Los cambios se aplican solo al idioma activo.</p>
            </div>
        </div>

        <form class="pure-form translation-form" action="{{ url('/') . '?' . http_build_query(['page' => 'language', 'id' => $stallId, 'codigo_idioma' => $currentLang]) }}" method="POST" id="formulario" aria-labelledby="formulario-titulo">
            @csrf
            <input type="hidden" name="csrf" value="{{ csrf_token() }}">

            <label for="tipo">Tipo <span class="admin-required" aria-hidden="true">*</span></label>
            <input type="text" id="tipo" name="tipo" value="{{ html_entity_decode((string) ($translation->tipo ?? '')) }}" placeholder="Tipo de puesto" required aria-required="true">

            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" cols="10" rows="10" placeholder="Descripción del puesto" maxlength="450">{{ html_entity_decode((string) ($translation->descripcion ?? '')) }}</textarea>

            <div class="translation-form__footer">
                <p class="admin-note">Máximo 450 caracteres.</p>
                <input type="submit" value="Guardar traducción">
            </div>
        </form>
    </section>
</main>
@endsection

@push('scripts')
<script type="module" src="{{ url('/admin/js/edit_translations.js') }}"></script>
@endpush