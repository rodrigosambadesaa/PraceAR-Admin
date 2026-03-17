const VERIFY_MALICIOUS_PHOTO_ENDPOINT = `${window.BASE_URL}helpers/verify_malicious_photo.php`;
export async function verifyMaliciousPhoto(file) {
  try {
    const formData = new FormData();
    formData.append("imagen", file);
    const response = await fetch(VERIFY_MALICIOUS_PHOTO_ENDPOINT, {
      method: "POST",
      body: formData,
    });
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const result = await response.json();
    return {
      success: Boolean(result.success),
      isMalicious: Boolean(result.is_malicious ?? result.isMalicious),
      message: result.message ?? "",
    };
  } catch (error) {
    console.error("Error al comprobar la foto:", error);
    return {
      success: false,
      isMalicious: false,
      message:
        "Error al contactar con el servicio de verificación. Inténtelo de nuevo más tarde.",
    };
  }
}
