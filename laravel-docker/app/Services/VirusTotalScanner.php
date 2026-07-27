<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class VirusTotalScanner
{
    /**
     * @return array{success:bool,is_malicious:bool,message:string,http_status:int}
     */
    public function scan(string $path): array
    {
        $apiKey = trim((string) config('services.virustotal.key', ''));
        $required = (bool) config('services.virustotal.required', true);

        if ($apiKey === '') {
            if ($required) {
                throw new RuntimeException('El análisis antimalware no está configurado; la carga se ha bloqueado de forma segura.');
            }

            return [
                'success' => true,
                'is_malicious' => false,
                'message' => 'Análisis externo desactivado por configuración.',
                'http_status' => 200,
            ];
        }

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('No se pudo leer el archivo temporal.');
        }

        try {
            $upload = Http::withHeaders(['x-apikey' => $apiKey])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(30)
                ->retry(2, 250, throw: false)
                ->attach('file', fopen($path, 'rb'), 'upload.jpg')
                ->post('https://www.virustotal.com/api/v3/files');

            if (!$upload->successful()) {
                throw new RuntimeException('El servicio antimalware no está disponible; la carga se ha bloqueado.');
            }

            $analysisId = (string) $upload->json('data.id', '');
            if ($analysisId === '') {
                throw new RuntimeException('El servicio antimalware devolvió una respuesta incompleta.');
            }

            for ($attempt = 0; $attempt < 6; $attempt++) {
                if ($attempt > 0) {
                    usleep(500_000);
                }

                $analysis = Http::withHeaders(['x-apikey' => $apiKey])
                    ->acceptJson()
                    ->connectTimeout(5)
                    ->timeout(15)
                    ->get('https://www.virustotal.com/api/v3/analyses/' . rawurlencode($analysisId));

                if (!$analysis->successful()) {
                    continue;
                }

                $status = (string) $analysis->json('data.attributes.status', '');
                if ($status !== 'completed') {
                    continue;
                }

                $malicious = (int) $analysis->json('data.attributes.stats.malicious', 0)
                    + (int) $analysis->json('data.attributes.stats.suspicious', 0);

                return [
                    'success' => true,
                    'is_malicious' => $malicious > 0,
                    'message' => $malicious > 0
                        ? 'La imagen ha sido rechazada por el análisis antimalware.'
                        : 'La imagen ha superado el análisis antimalware.',
                    'http_status' => 200,
                ];
            }
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable) {
            // Expose no network or provider detail to the browser.
        }

        throw new RuntimeException('No se pudo completar el análisis antimalware; la carga se ha bloqueado.');
    }
}
