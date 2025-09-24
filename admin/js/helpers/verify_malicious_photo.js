"use strict";

const VERIFY_MALICIOUS_PHOTO_ENDPOINT = new URL(
    '../helpers/verify_malicious_photo.php',
    window.location.href
).toString();

/**
 * Solicita al backend que verifique si un archivo contiene código malicioso.
 *
 * @async
 * @param {File} photo Archivo a analizar.
 * @returns {Promise<{success: boolean, isMalicious: boolean, message: string}>}
 */
export async function verifyMaliciousPhoto(file) {
    try {
        // Crear FormData con el archivo
        const formData = new FormData();
        formData.append('imagen', file);

        // Realizar petición al endpoint de verificación
        // Corregir la ruta para incluir el directorio del proyecto
        const response = await fetch('/appventurers/helpers/verify_malicious_photo.php', {
            method: 'POST',
            body: formData
        });

        // Verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Obtener respuesta JSON
        const result = await response.json();

        return {
            success: result.success,
            isMalicious: result.is_malicious || false,
            message: result.message || ''
        };

    } catch (error) {
        console.error('Error al comprobar la foto:', error);

        return {
            success: false,
            isMalicious: false,
            message: 'Error al contactar con el servicio de verificación. Inténtelo de nuevo más tarde.'
        };
    }
}