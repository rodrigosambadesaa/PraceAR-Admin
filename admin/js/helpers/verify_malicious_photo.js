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
async function verifyMaliciousPhoto(photo) {
    if (!(photo instanceof File)) {
        return {
            success: false,
            isMalicious: false,
            message: 'No se ha proporcionado un archivo válido para su verificación.'
        };
    }

    const formData = new FormData();
    formData.append('file', photo);

    try {
        const response = await fetch(VERIFY_MALICIOUS_PHOTO_ENDPOINT, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            return {
                success: false,
                isMalicious: false,
                message: data.message || `No se pudo validar la foto (HTTP ${response.status}).`
            };
        }

        return {
            success: true,
            isMalicious: Boolean(data.is_malicious),
            message: data.message || ''
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

export { verifyMaliciousPhoto };