import API_KEY from './api_key.js';

/**
 * Verifies if a photo is malicious by sending it to the VirusTotal API for scanning.
 *
 * @async
 * @function verifyMaliciousPhoto
 * @param {File} photo - The photo file to be checked for malicious content.
 * @returns {Promise<boolean>} A promise that resolves to `true` if the photo is detected as malicious, 
 *                             or `false` otherwise.
 * @throws {Error} If there is an issue with the API request or response processing.
 */
async function verifyMaliciousPhoto(photo) {
    // Verificar mediante llamada a la API de VirusTotal si la foto es maliciosa

    // Crear un objeto FormData con la foto
    const formData = new FormData();
    formData.append('file', photo);

    // Enviar la foto a VirusTotal
    try {
        const response = await fetch('https://www.virustotal.com/vtapi/v2/file/scan', {
            method: 'POST',
            body: formData,
            headers: {
                'x-apikey': API_KEY // Usar la API key de VirusTotal
            }
        });

        // Leer y procesar la respuesta
        const data = await response.json();

        return data.response_code === 1 && data.positives > 0;

    } catch (error) {
        console.error('Error al comprobar la foto:', error);
        return false;
    }
}

export { verifyMaliciousPhoto };