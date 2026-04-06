@extends('layouts.admin')

@section('title', 'Admin - PraceAR - Editar puesto')
@section('activePage', 'edit')
@section('bodyClass', 'admin-edit')

@push('styles')
<link rel="stylesheet" href="{{ url('/admin/css/edit_admin.css') }}">
<link rel="stylesheet" href="{{ url('/admin/css/edit_admin_redesign.css') }}">
@endpush

@section('content')
<main class="admin-page-main edit-layout">
    <section class="edit-hero" aria-labelledby="edit-page-title">
        <p class="admin-eyebrow">Ficha del puesto</p>
        <h2 id="edit-page-title" class="admin-page-title">Editar datos generales</h2>
        <p class="edit-subtitle">Actualiza la información comercial, de ubicación e imagen de este puesto.</p>
        <div class="admin-metadata">
            <span class="admin-chip">Caseta {{ $stall->caseta }}</span>
            <span class="admin-chip">{{ $stall->activo ? 'Activo' : 'Inactivo' }}</span>
            <span class="admin-chip">{{ $imageExists ? 'Con imagen' : 'Sin imagen' }}</span>
            <span class="admin-chip">Idioma {{ strtoupper($currentLang) }}</span>
        </div>
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

    <section class="edit-grid">
        <article class="edit-card">
            <form action="{{ url('/') . '?' . http_build_query(['page' => 'edit', 'id' => $stall->id, 'lang' => $currentLang]) }}" method="POST" class="form-group" id="formulario-editar" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="csrf" value="{{ csrf_token() }}">

                <div class="grid">
                    <div class="switch-field">
                        <label for="activo">Estado del puesto</label>
                        <label class="switch-control" for="activo">
                            <input type="checkbox" id="activo" name="activo" value="1" @checked($stall->activo)>
                            <span>Activo</span>
                        </label>
                    </div>
                    <div>
                        <p class="field-hint">Puedes desactivar temporalmente el puesto sin borrar sus datos.</p>
                    </div>
                </div>

                @if ($imageExists)
                <span class="section-label">Imagen actual</span>
                <div class="image-preview-wrapper">
                    <img src="{{ url('/' . \App\Support\PracearSupport::imagePublicPath((string) $stall->caseta)) }}" alt="Imagen del puesto {{ $stall->nombre }}" class="zoomable">
                </div>

                <div class="image-actions">
                    <label for="imagen">Reemplazar imagen (.jpg, máx 2MB)</label>
                    <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg" aria-label="Reemplazar imagen">
                </div>

                <div class="image-actions">
                    <label for="eliminar-imagen">
                        <input type="checkbox" id="eliminar-imagen" name="eliminar_imagen" value="1">
                        Eliminar imagen actual
                    </label>
                </div>
                @else
                <label for="imagen">Subir Imagen (.jpg, máx 2MB)</label>
                <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg" aria-label="Subir imagen">
                @endif

                <div class="grid">
                    <div>
                        <label for="caseta">Caseta</label>
                        <input type="text" id="caseta" name="caseta" value="{{ $stall->caseta }}" readonly aria-label="Código de caseta">
                    </div>

                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ $stall->nombre }}" placeholder="Nombre del puesto" aria-label="Nombre del puesto">
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <label for="contacto">Información de Contacto</label>
                        <input type="text" id="contacto" name="contacto" aria-label="Información de contacto" value="{{ $stall->contacto }}">
                    </div>

                    <div>
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ $stall->telefono }}" placeholder="Teléfono de contacto. Por ejemplo: 981 123 456" aria-label="Teléfono de contacto">
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <label for="tipo-unity">Tipo en Unity <span class="admin-required" aria-hidden="true">*</span></label>
                        <select name="tipo_unity" id="tipo-unity" aria-required="true">
                            @foreach ($unityTypes as $key => $label)
                            <option value="{{ $key }}" @selected(($stall->tipo_unity ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id-nave">ID Nave <span class="admin-required" aria-hidden="true">*</span></label>
                        <select required id="id-nave" name="id_nave" aria-required="true">
                            @foreach ($naves as $nave)
                            <option value="{{ $nave->id }}" @selected((int) $stall->id_nave === (int) $nave->id)>{{ $nave->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="caseta-padre">Caseta padre</label>
                    <input name="caseta_padre" type="text" id="caseta-padre" value="{{ $stall->caseta_padre }}" placeholder="Código de caseta padre" aria-label="Caseta padre">
                </div>

                <div id="div-botones">
                    <input id="actualizar" type="submit" value="Guardar cambios" aria-label="Actualizar datos del puesto">
                </div>
            </form>
        </article>

        <aside class="edit-side-card admin-section-card">
            <p class="admin-eyebrow">Resumen rápido</p>
            <h3>{{ $stall->nombre ?: 'Sin nombre' }}</h3>
            <dl class="edit-side-card__stats">
                <div>
                    <dt>Caseta</dt>
                    <dd>{{ $stall->caseta }}</dd>
                </div>
                <div>
                    <dt>Nave</dt>
                    <dd>{{ $stall->id_nave }}</dd>
                </div>
                <div>
                    <dt>Contacto</dt>
                    <dd>{{ $stall->contacto ?: 'Sin dato' }}</dd>
                </div>
                <div>
                    <dt>Padre</dt>
                    <dd>{{ $stall->caseta_padre ?: 'Ninguno' }}</dd>
                </div>
            </dl>
            <ul class="admin-guidance-list">
                <li>La caseta se mantiene como referencia interna y no se edita desde aquí.</li>
                <li>Las traducciones siguen estando en su pantalla específica.</li>
                <li>La imagen admite solo archivos JPG o JPEG.</li>
            </ul>
        </aside>
    </section>

    <p class="note admin-note">Los campos marcados con <span class="admin-required" aria-hidden="true">*</span> son obligatorios.</p>

    <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-hidden="true">
        <button id="zoomed-close" class="zoomed-close" aria-label="Cerrar imagen ampliada">&times;</button>
        <img id="zoomed-image" src="" alt="">
    </div>
</main>
@endsection

@push('scripts')
<script type="module" src="{{ url('/admin/js/edit_stall.js') }}"></script>
@endpush