@extends('layouts.admin')

@section('title', 'Admin - PraceAR - Panel principal')
@section('activePage', 'index')
@section('bodyClass', 'admin-index')

@push('styles')
<link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/admin/css/index_admin.css' }}">
<link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/admin/css/index_admin_redesign.css' }}">
<link rel="stylesheet" href="{{ rtrim($baseUrl, '/') . '/admin/css/index_modal.css' }}">
@endpush

@section('content')
@php
$visibleCount = $stalls->count();
$totalCount = method_exists($stalls, 'total') ? $stalls->total() : $visibleCount;
@endphp
<main class="admin-page-main admin-index-main" role="main">
    <section class="admin-page-intro" aria-labelledby="admin-index-title">
        <div class="admin-page-intro__panel">
            <p class="admin-eyebrow">Gestión de puestos</p>
            <h2 id="admin-index-title" class="admin-page-title">Tabla principal del mercado</h2>
            <p class="admin-page-lead">Busca por código, edita datos en línea y entra en cada ficha cuando necesites revisar la información completa.</p>
            <div class="admin-metadata">
                <span class="admin-chip">{{ number_format($totalCount) }} registros</span>
                <span class="admin-chip">{{ number_format($visibleCount) }} visibles</span>
                <span class="admin-chip">Idioma {{ strtoupper($currentLang) }}</span>
                <span class="admin-chip">{{ $searchPerformed ? 'Filtro activo' : 'Vista completa' }}</span>
            </div>
        </div>
        <aside class="admin-page-intro__aside">
            <p class="admin-eyebrow">Operativa diaria</p>
            <h3>Desde aquí puedes</h3>
            <ul class="admin-guidance-list">
                <li>Localizar puestos rápidamente por su código.</li>
                <li>Editar texto, contacto, nave e imagen sin salir de la tabla.</li>
                <li>Abrir la traducción del puesto con un clic.</li>
            </ul>
        </aside>
    </section>

    @if (session('status') || $errors->any())
    <div class="admin-feedback-stack">
        @if (session('status'))
        <div class="admin-alert admin-alert--success">
            <p>{{ session('status') }}</p>
        </div>
        @endif

        @foreach ($errors->all() as $error)
        <div class="admin-alert admin-alert--error">
            <p>{{ $error }}</p>
        </div>
        @endforeach
    </div>
    @endif

    @if ($resultsFound)
    <section class="admin-section-card admin-index-card">
        <div id="cabecera-tabla" class="admin-section-card__header admin-index-toolbar">
            <div>
                <p class="admin-eyebrow">Listado operativo</p>
                <h2 id="texto-cabecera-tabla">Lista de puestos del Mercado de Abastos</h2>
                <p>La tabla mantiene el flujo original, pero con una cabecera y controles más claros.</p>
            </div>
            <div class="admin-index-toolbar__meta">
                <span class="admin-chip">Pág. {{ $stalls->currentPage() }} de {{ $stalls->lastPage() }}</span>
                <span class="admin-chip">Búsqueda {{ $searchPerformed ? 'aplicada' : 'libre' }}</span>
                <span id="admin-index-language-chip" class="admin-chip">Idioma {{ strtoupper($currentLang) }}</span>
            </div>
            <div class="admin-index-toolbar__language" id="table-language-controls" data-current-lang="{{ $currentLang }}">
                <label for="table-language-selector">Idioma activo para traducciones y edición rápida</label>
                <select id="table-language-selector" aria-label="Seleccionar idioma de trabajo en la tabla">
                    @foreach ($languages as $languageCode => $languageLabel)
                    <option value="{{ $languageCode }}" @selected($languageCode===$currentLang)>{{ $languageLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="contenedor-separacion" aria-hidden="true"></div>

        <search class="admin-index-search" role="search">
            <form id="formulario-busqueda" class="admin-search-form" action="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => 1, 'lang' => $currentLang]) }}" method="POST"
                data-search-executed="{{ $searchPerformed ? 'true' : 'false' }}">
                @csrf
                <input value="{{ $search }}" type="text" id="input-busqueda"
                    placeholder="Código de caseta. P. ej. CE001, CO121, MC001, NA338, NC041" name="caseta"
                    @if (!$searchPerformed) autofocus @endif>
                <input type="hidden" name="lang" id="lang" value="{{ $currentLang }}">
                <input type="submit" value="Buscar">
                <input id="input-reseteo" name="input_reseteo" type="reset" value="Reiniciar">
                <input id="input-deshacer-busqueda" type="button" value="Deshacer"
                    data-redirect-url="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['lang' => $currentLang]) }}">
                <input type="hidden" name="csrf" id="csrf" value="{{ csrf_token() }}">
            </form>
        </search>

        <div class="admin-index-pagination admin-index-pagination--top">
            @include('admin.partials.pagination', ['paginator' => $stalls, 'currentLang' => $currentLang])
        </div>

        <div class="admin-table-layout">
            <aside class="table-language-dock" aria-labelledby="table-language-dock-title">
                <div class="table-language-dock__heading">
                    <span class="table-language-dock__eyebrow">Vista de tabla</span>
                    <strong id="table-language-dock-title">Idioma</strong>
                    <span class="table-language-dock__current">{{ strtoupper($currentLang) }}</span>
                </div>
                @include('admin.partials.language_selector', [
                    'activePage' => 'index',
                    'currentLang' => $currentLang,
                    'languages' => $languages,
                    'rootUrl' => rtrim($baseUrl, '/') . '/',
                ])
            </aside>

            <div class="admin-table-shell" role="region" aria-label="Tabla de puestos con desplazamiento horizontal">
                <table id="tabla-puestos" role="table" aria-label="Lista de puestos del Mercado de Abastos">
                <caption class="sr-only">Lista de puestos del Mercado de Abastos</caption>
                <thead>
                    <tr>
                        <th scope="col">Editar</th>
                        <th scope="col">Activo</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Caseta</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Tipo Unity</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">ID Nave</th>
                        <th scope="col">Puesto padre</th>
                        <th scope="col" id="celda-especial"></th>
                        <th scope="col">Editar Traducción</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stalls as $stall)
                    <tr id="row-{{ $stall->id }}">
                        <td scope="row" data-label="Editar">
                            <a href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => 'edit', 'id' => $stall->id, 'lang' => $currentLang]) }}"
                                aria-label="Editar puesto {{ $stall->caseta }}">
                                <img loading="lazy" width="30" height="30" src="{{ rtrim($baseUrl, '/') . '/img/pencil.png' }}" alt="Editar">
                            </a>
                        </td>
                        <td data-label="Activo">{{ $stall->activo ? 'Sí' : 'No' }}</td>
                        <td data-label="Imagen">
                            @php
                            $imageExists = is_file(\App\Support\PracearSupport::imageDiskPath((string) $stall->caseta));
                            $imageUrl = \App\Support\PracearSupport::imageUrl((string) $stall->caseta);
                            @endphp
                            @if ($imageExists)
                            <img loading="lazy" class="zoomable editable-image" src="{{ $imageUrl }}"
                                alt="Imagen del puesto {{ $stall->caseta }}" data-editable="true" data-field="imagen"
                                data-id="{{ $stall->id }}" data-exists="1">
                            @else
                            <div class="editable-image-blank" data-editable="true" data-field="imagen" data-id="{{ $stall->id }}">
                                Subir imagen
                            </div>
                            @endif
                        </td>
                        <td data-label="Caseta" data-field="caseta">{{ $stall->caseta }}</td>
                        <td data-label="Nombre" data-editable="true" data-field="nombre" data-id="{{ $stall->id }}">{{ $stall->nombre }}</td>
                        <td data-label="Tipo Unity">{{ $stall->tipo_unity }}</td>
                        <td data-label="Información de Contacto" data-editable="true" data-field="contacto" data-id="{{ $stall->id }}">{{ $stall->contacto }}</td>
                        <td data-label="Teléfono" data-editable="true" data-field="telefono" data-id="{{ $stall->id }}">{{ $stall->telefono }}</td>
                        <td data-label="ID Nave" data-editable="true" data-field="id_nave" data-id="{{ $stall->id }}">{{ $stall->id_nave }}</td>
                        <td data-label="Puesto padre" data-editable="true" data-field="caseta_padre" data-id="{{ $stall->id }}">{{ $stall->caseta_padre ?: 'Ninguno' }}</td>
                        <td data-label="" id="celda-especial"></td>
                        <td data-label="Editar Traducción">
                            <a data-translation-link="true" href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => 'language', 'id' => $stall->id, 'codigo_idioma' => $currentLang]) }}"
                                aria-label="Editar traducción de {{ $stall->caseta }}">
                                <img src="{{ rtrim($baseUrl, '/') . '/img/flags/' . $currentLang . '.png' }}" alt="Editar traducción" width="18" height="18">
                            </a>
                        </td>
                        <td data-label="Tipo" class="fondo-color-diferente editable-tipo" data-editable="true" data-field="tipo"
                            data-id="{{ $stall->id }}" data-codigo_idioma="{{ $currentLang }}">
                            {{ html_entity_decode((string) ($stall->tipo ?: 'Sin tipo')) }}
                        </td>
                        <td data-label="Descripción" class="fondo-color-diferente editable-descripcion" data-editable="true"
                            data-field="descripcion" data-id="{{ $stall->id }}" data-codigo_idioma="{{ $currentLang }}">
                            {{ \Illuminate\Support\Str::limit(html_entity_decode((string) ($stall->descripcion ?: 'Sin descripción')), 30) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section-card__footer admin-index-pagination" role="contentinfo">
            @include('admin.partials.pagination', ['paginator' => $stalls, 'currentLang' => $currentLang])
        </div>
    </section>
    @else
    <section class="admin-section-card admin-empty-state">
        <p class="admin-eyebrow">Sin coincidencias</p>
        <h2>No se encontraron resultados para <strong>{{ $search }}</strong>.</h2>
        <p>Prueba con otro código de caseta o revisa la ortografía.</p>
        <ul>
            <li>Ejemplo: <code>CE001</code>, <code>CO121</code>, <code>MC001</code>, <code>NA338</code>, <code>NC041</code>.</li>
            <li>Si quieres ver todos los puestos, deja el campo vacío y vuelve a buscar.</li>
        </ul>
    </section>
    @endif
</main>

<div id="modal-edicion" class="modal-edicion">
    <div id="modal-content">
        <button id="modal-close" class="zoomed-close close-button" aria-label="Cerrar edición rápida">&times;</button>
        <div id="modal-body"></div>
    </div>
</div>

<div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-labelledby="zoomed-name" aria-hidden="true">
    <button class="close-button" aria-label="Cerrar vista ampliada">&times;</button>
    <img id="zoomed-image" src="" alt="Imagen ampliada del puesto">
    <p id="zoomed-name"></p>
</div>
@endsection

@push('scripts')
@if ($resultsFound)
<script type="module" src="{{ rtrim($baseUrl, '/') . '/admin/js/index.js' }}"></script>
@endif

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.querySelectorAll('.zoomable.editable-image').forEach((img) => {
        img.addEventListener('click', () => {
            if (img.dataset.exists === '1') {
                document.getElementById('zoomed-image').src = img.src;
                document.getElementById('zoomed-name').textContent = img.alt;
                document.getElementById('zoomed-image-container').classList.add('show');
                document.getElementById('zoomed-image-container').setAttribute('aria-hidden', 'false');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const inputBusqueda = document.getElementById('input-busqueda');
        const tableLanguageSelector = document.getElementById('table-language-selector');
        const formulario = inputBusqueda?.closest('form');
        if (!inputBusqueda || !formulario) {
            return;
        }

        const sugerenciasDiv = document.createElement('div');
        sugerenciasDiv.id = 'sugerencias-busqueda';
        sugerenciasDiv.style.display = 'none';
        inputBusqueda.parentNode.style.position = 'relative';
        inputBusqueda.parentNode.appendChild(sugerenciasDiv);

        inputBusqueda.addEventListener('input', function() {
            const termino = this.value.trim();
            const selectedLanguage =
                tableLanguageSelector instanceof HTMLSelectElement && tableLanguageSelector.value ?
                tableLanguageSelector.value :
                '{{ $currentLang }}';
            if (termino.length < 2) {
                sugerenciasDiv.style.display = 'none';
                return;
            }

            fetch(`${window.BASE_URL}admin/ajax_sugerencias.php?caseta=${encodeURIComponent(termino)}&lang=${encodeURIComponent(selectedLanguage)}`)
                .then((response) => response.json())
                .then((data) => {
                    sugerenciasDiv.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach((suggestion) => {
                            const item = document.createElement('div');
                            item.textContent = suggestion;
                            item.tabIndex = 0;
                            item.addEventListener('mousedown', (event) => {
                                event.preventDefault();
                                inputBusqueda.value = suggestion;
                                sugerenciasDiv.style.display = 'none';
                                formulario.submit();
                            });
                            sugerenciasDiv.appendChild(item);
                        });
                        sugerenciasDiv.style.display = 'block';
                    } else {
                        sugerenciasDiv.innerHTML = '<div class="search-suggestions__empty">Sin sugerencias</div>';
                        sugerenciasDiv.style.display = 'block';
                    }
                });
        });

        inputBusqueda.addEventListener('blur', () => {
            setTimeout(() => {
                sugerenciasDiv.style.display = 'none';
            }, 120);
        });

        inputBusqueda.addEventListener('focus', () => {
            if (sugerenciasDiv.innerHTML.trim()) {
                sugerenciasDiv.style.display = 'block';
            }
        });
    });
</script>
@endpush
