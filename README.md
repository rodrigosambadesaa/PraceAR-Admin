*# PraceAR

## Aviso importante — Versión personal no oficial
Este repositorio contiene una **versión personal mejorada** de la web desarrollada inicialmente en el marco del *Obradoiro de Emprego APPVENTURERS, Departamento de Emprego do Concello de Santiago de Compostela, octubre 2023 - octubre 2024*.  
- Autor de esta versión: **Rodrigo Sambade Saá**
- Autor de la versión original finalizada durante el obradoiro y publicada en [PraceAR](https://pracear.com): **Rodrigo Sambade Saá**, con supervisión del profesor de programación **Jorge (Xurxo) González Tenreiro**, cuyo nombre de usuario en GitHub es **_webferrol_**
- Estado: **Versión personal / no oficial**.  
- Propiedad original: El proyecto inicial pertenece a la entidad promotora del obradoiro, que puede ostentar derechos sobre la versión oficial.  
- Finalidad: Documentar correcciones técnicas y mejoras de seguridad realizadas a título personal.  
- Si representa a la entidad titular y desea discutir la integración o retirada de este contenido, contacte en: **[rsamsaa.appventurers@gmail.com]**.  

Clonar el repositorio en una carpeta llamada appventurers

## Requisitos de la App

![Requisitos](https://github.com/user-attachments/assets/2ba5d275-9420-436a-bc1a-619ddfcd072d)

## Guía de uso

- Lo primero que debe hacer es importar la base de datos, crear un usuario con su contraseña encriptada en Argon2ID, configurar los archivos que hacen referencia a la API Key de VirusTotal e iniciar sesión.
- Tras iniciar sesión se le redigirirá a la tabla de administración donde podrá buscar puestos, cambiar de idioma, editar un puesto o una traducción del mismo, cambiar su contraseña o ver un mapa de las ameas, naves y murallones

### Verificación de imágenes con VirusTotal

Para evitar que se suban archivos maliciosos se integra una comprobación automática con la API de VirusTotal:

1. **Configurar la clave**: cree un archivo `virustotal_api_key.php` en la raíz del proyecto con la constante `VIRUSTOTAL_API_KEY` que contenga su clave de API.
2. **Flujo de validación**: cuando el formulario de edición sube una imagen, el frontend llama a `helpers/verify_malicious_photo.php`, que reenvía el archivo temporal a VirusTotal usando `curl_file_create`, manteniendo activos `CURLOPT_SSL_VERIFYPEER` y `CURLOPT_SSL_VERIFYHOST` y aplicando timeouts razonables.
4. **Validación en el servidor**: además del filtro en el navegador, `helpers/update_stalls.php` invoca la misma comprobación antes de aceptar definitivamente la imagen.

Si no se define la clave de la API o el servicio devuelve un error, el usuario recibirá un mensaje explicativo y la imagen no se subirá.


### Página de inicio de sesión
![Captura de pantalla (7)](https://github.com/user-attachments/assets/f28819db-32a8-478c-845b-8734242db901)


### Página de administración
![Captura de pantalla (8)](https://github.com/user-attachments/assets/7ce0f84c-870e-4f23-aea1-d8749267e46e)


### Página de administración con una búsqueda hecha
![Captura de pantalla (9)](https://github.com/user-attachments/assets/c95b6b92-91b6-4a4e-90bc-def0e3945bab)


### Página de edición de datos generales
![Captura de pantalla (10)](https://github.com/user-attachments/assets/027a3c50-3d5c-40fa-9771-a1031352edc8)


### Página de edición de tipo y traducción
![Captura de pantalla (11)](https://github.com/user-attachments/assets/f361824b-7f2e-4edd-ac89-fe455f27b84b)

### Imagen y nombre de un puesto ampliados en la página de administración (haciendo clic sobre la imagen)
![Captura de pantalla (12)](https://github.com/user-attachments/assets/b01259cd-21c2-4531-b58e-c97a89e6094e)


### Página de naves con mapas de las ameas, naves y murallones
![Captura de pantalla (13)](https://github.com/user-attachments/assets/85d2eaef-dd51-4808-a089-963a2096c229)


### Imagen de un mapa ampliada en la página de naves con el nombre y el rango de naves correspondientes (como antes, para ampliar hacer clic en la imagen o en el texto)
![Captura de pantalla (14)](https://github.com/user-attachments/assets/6464bdb6-fc6a-43c0-b171-190721d03445)


### Página de cambio de contraseña
![Captura de pantalla (15)](https://github.com/user-attachments/assets/f4e8ad4e-3371-43c4-9388-6c2e26883e01)


*