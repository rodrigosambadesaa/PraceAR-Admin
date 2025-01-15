import { apiKey } from './api_key.js';

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
                'x-apikey': apiKey
            }
        });

        // Leer y procesar la respuesta
        const data = await response.json();
        console.log(`Datos recibidos de la API:\n${JSON.stringify(data)}`);

        return data.response_code === 1 && data.positives > 0;

    } catch (error) {
        console.error('Error al comprobar la foto:', error);
        return false;
    }
}

export { verifyMaliciousPhoto };