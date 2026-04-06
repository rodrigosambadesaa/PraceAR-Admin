<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transicion Laravel</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(145deg, #f6f8fb, #e9eef8);
            color: #1f2937;
        }

        .container {
            max-width: 840px;
            margin: 48px auto;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.1);
            padding: 24px;
        }

        h1 {
            margin-top: 0;
            font-size: 1.9rem;
        }

        ul {
            padding-left: 20px;
            line-height: 1.8;
        }

        a {
            color: #0f4ea8;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        .hint {
            margin-top: 20px;
            font-size: 0.95rem;
            color: #4b5563;
        }
    </style>
</head>

<body>
    <main class="container">
        <section class="card">
            <h1>Transicion gradual a Laravel</h1>
            <p>Esta instancia Laravel actua como capa de entrada y deriva modulos todavia en PHP legado.</p>
            <ul>
                <li><a href="{{ route('legacy.home') }}">Inicio legado</a></li>
                <li><a href="{{ route('legacy.login') }}">Login legado</a></li>
                <li><a href="{{ route('legacy.admin') }}">Admin legado</a></li>
            </ul>
            <p class="hint">Configura LEGACY_BASE_URL en el archivo .env para apuntar al host donde sirve la app PHP actual.</p>
        </section>
    </main>
</body>

</html>