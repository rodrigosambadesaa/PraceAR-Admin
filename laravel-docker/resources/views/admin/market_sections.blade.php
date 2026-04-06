@extends('layouts.admin')

@section('title', 'Admin - PraceAR - Mapas')
@section('activePage', 'market_sections')
@section('bodyClass', 'admin-market-sections')

@push('styles')
<link rel="stylesheet" href="{{ url('/market_sections.css') }}">
<link rel="stylesheet" href="{{ url('/market_sections_redesign.css') }}">
@endpush

@section('content')
<main class="admin-page-main maps-page">
    <section class="admin-page-intro" aria-labelledby="maps-page-title">
        <div class="admin-page-intro__panel">
            <p class="admin-eyebrow">Cartografía interna</p>
            <h2 id="maps-page-title" class="admin-page-title">Mapas de ameas, naves y murallones</h2>
            <p class="admin-page-lead">Consulta la distribución visual del recinto y amplía cada plano cuando necesites revisar una sección concreta.</p>
            <div class="admin-metadata">
                <span class="admin-chip">{{ count($images) }} planos</span>
                <span class="admin-chip">Vista ampliable</span>
                <span class="admin-chip">Soporte visual</span>
            </div>
        </div>
        <aside class="admin-page-intro__aside">
            <p class="admin-eyebrow">Uso recomendado</p>
            <h3>Cuándo consultar esta pantalla</h3>
            <ul class="admin-guidance-list">
                <li>Al verificar la ubicación física de un puesto o de una nave.</li>
                <li>Al responder dudas de distribución dentro del mercado.</li>
                <li>Al contrastar el identificador de nave con el plano general.</li>
            </ul>
        </aside>
    </section>

    <section class="admin-section-card maps-card">
        <div class="maps-grid">
            @foreach ($images as $image)
            <figure class="zoom" tabindex="0" role="button" aria-label="Ampliar {{ $image['alt'] }}">
                <img loading="lazy" src="{{ $image['src'] }}" alt="{{ $image['alt'] }}">
                <figcaption>{{ $image['caption'] }}</figcaption>
            </figure>
            @endforeach
        </div>
    </section>
</main>

<div id="zoomed-container" class="zoomed-container" role="dialog" aria-hidden="true" aria-labelledby="zoomed-caption">
    <button id="zoomed-close" class="zoomed-close" aria-label="Cerrar imagen ampliada">&times;</button>
    <figure>
        <img id="zoomed-image" src="" alt="">
        <figcaption id="zoomed-caption"></figcaption>
    </figure>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ url('/admin/js/market_sections.js') }}"></script>
@endpush