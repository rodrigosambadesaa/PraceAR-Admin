<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once(__DIR__ . '/../../helpers/verify_strong_password.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit;
}

$min_length = 16;
$max_length = 1024;

$length = isset($_POST['length']) ? (int)$_POST['length'] : $min_length;
if ($length < $min_length) $length = $min_length;
if ($length > $max_length) $length = $max_length;

$mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$minusculas = 'abcdefghijklmnopqrstuvwxyz';
$numeros = '0123456789';
$especiales = '!@#$%^&*()-_=+[]{}|;:,.<>?';
$todos = $mayusculas . $minusculas . $numeros . $especiales;

// Genera una subcontraseña segura
function generar_subcontrasena($length, $mayusculas, $minusculas, $numeros, $especiales, $todos) {
    while (true) {
        $password_array = [];
        // Garantiza requisitos mínimos
        $password_array[] = $mayusculas[random_int(0, strlen($mayusculas) - 1)];
        $password_array[] = $minusculas[random_int(0, strlen($minusculas) - 1)];
        $password_array[] = $numeros[random_int(0, strlen($numeros) - 1)];
        // Tres caracteres especiales distintos
        $especiales_usados = [];
        while (count($especiales_usados) < 3) {
            $c = $especiales[random_int(0, strlen($especiales) - 1)];
            if (!in_array($c, $especiales_usados)) {
                $especiales_usados[] = $c;
            }
        }
        $password_array = array_merge($password_array, $especiales_usados);

        // Rellena el resto
        while (count($password_array) < $length) {
            $password_array[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        shuffle($password_array);
        $password = implode('', $password_array);

        if (
            es_contrasenha_fuerte($password)
            && !tiene_secuencias_alfabeticas_inseguras($password)
            && !tiene_secuencias_numericas_inseguras($password)
            && !tiene_secuencias_caracteres_especiales_inseguras($password)
            && !tiene_espacios_al_principio_o_al_final($password)
        ) {
            return $password;
        }
        // Si no cumple, vuelve a intentar
    }
}

// Si la longitud es muy grande, divide en 3 subcontraseñas y concatena
if ($length > 256) {
    while (true) {
        $partes = 3;
        $longitud_parte = intdiv($length, $partes);
        $resto = $length % $partes;
        $password_final = '';
        for ($i = 0; $i < $partes; $i++) {
            $l = $longitud_parte + ($i === $partes - 1 ? $resto : 0);
            $sub = generar_subcontrasena($l, $mayusculas, $minusculas, $numeros, $especiales, $todos);
            $password_final .= $sub;
        }
        // Validación final
        if (
            es_contrasenha_fuerte($password_final)
            && !tiene_secuencias_alfabeticas_inseguras($password_final)
            && !tiene_secuencias_numericas_inseguras($password_final)
            && !tiene_secuencias_caracteres_especiales_inseguras($password_final)
            && !tiene_espacios_al_principio_o_al_final($password_final)
            && !es_contrasenha_antigua($password_final, $_SESSION['nombre_usuario'])
        ) {
            echo json_encode([
                'success' => true,
                'password' => $password_final,
                'stats' => [
                    'uppercase' => contar_mayusculas($password_final),
                    'lowercase' => contar_minusculas($password_final),
                    'digits' => contar_digitos($password_final),
                    'special' => contar_caracteres_especiales($password_final),
                    'entropy' => entropia($password_final),
                    'hashResistanceTime' => tiempo_estimado_resistencia_ataque_fuerza_bruta($password_final),
                ]
            ]);
            exit;
        }
        // Si la concatenación genera una secuencia insegura, vuelve a intentar
    }
} else {
    // Para longitudes normales, generación directa
    $password = generar_subcontrasena($length, $mayusculas, $minusculas, $numeros, $especiales, $todos);
    echo json_encode([
        'success' => true,
        'password' => $password,
        'stats' => [
            'uppercase' => contar_mayusculas($password),
            'lowercase' => contar_minusculas($password),
            'digits' => contar_digitos($password),
            'special' => contar_caracteres_especiales($password),
            'entropy' => entropia($password),
            'hashResistanceTime' => tiempo_estimado_resistencia_ataque_fuerza_bruta($password),
        ]
    ]);
    exit;
}

