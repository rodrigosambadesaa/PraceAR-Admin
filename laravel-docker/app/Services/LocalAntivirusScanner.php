<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * A mandatory, on-demand antivirus on the *application server*.
 *
 * Linux/macOS: ClamAV (there is no supported XProtect on-demand CLI).
 * Windows: Microsoft Defender's MpCmdRun, with remediation and exclusions
 * disabled so a zero exit code is meaningful and the staged file is not changed.
 */
class LocalAntivirusScanner
{
    public function scan(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('No se pudo acceder a la imagen para el análisis local.');
        }

        [$driver, $binary] = $this->resolve();
        $command = $driver === 'defender'
            ? [$binary, '-Scan', '-ScanType', '3', '-DisableRemediation', '-File', $path]
            : [$binary, '--no-summary', '--stdout', $path];

        // Symfony Process uses an argument vector (not a shell command).
        // Never interpolate the file path into a shell or PowerShell expression.
        try {
            $process = new Process($command);
            $process->setTimeout(120);
            $process->run();
            $exitCode = $process->getExitCode();
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('El análisis antivirus local agotó el tiempo permitido.');
        } catch (Throwable) {
            throw new RuntimeException('No se pudo ejecutar el antivirus local; la carga está bloqueada.');
        }

        if ($exitCode === 0) {
            return;
        }

        if ($driver === 'clamav' && $exitCode === 1) {
            throw new InvalidArgumentException('El antivirus local ha detectado una amenaza en la imagen.');
        }

        // For ClamAV, exit 2 indicates an error. Defender exit 2 is ambiguous
        // (unremediated threat or scan error). Both must fail closed.
        throw new RuntimeException('El antivirus local no ha confirmado que la imagen sea segura.');
    }

    public function assertAvailable(): void
    {
        $this->resolve();
    }

    /**
     * @return array{0: 'clamav'|'defender', 1: string}
     */
    private function resolve(): array
    {
        $driver = strtolower(trim((string) config('services.local_antivirus.driver', 'auto')));
        if ($driver === 'auto') {
            $driver = PHP_OS_FAMILY === 'Windows' ? 'defender' : 'clamav';
        }

        if (!in_array($driver, ['clamav', 'defender'], true)) {
            throw new RuntimeException('El motor antivirus local configurado no es compatible.');
        }

        $override = trim((string) config('services.local_antivirus.binary', ''));
        if ($override !== '') {
            $binary = $override;
        } elseif ($driver === 'clamav') {
            $candidates = PHP_OS_FAMILY === 'Darwin'
                ? ['/opt/homebrew/bin/clamscan', '/usr/local/bin/clamscan', '/usr/bin/clamscan']
                : ['/usr/bin/clamscan', '/usr/local/bin/clamscan'];
            $binary = $this->firstExecutable($candidates);
        } else {
            $programData = rtrim((string) getenv('ProgramData'), '\\/');
            $programFiles = rtrim((string) getenv('ProgramFiles'), '\\/');
            $candidates = [];
            if ($programData !== '') {
                $versions = glob($programData . '/Microsoft/Windows Defender/Platform/*/MpCmdRun.exe') ?: [];
                rsort($versions, SORT_NATURAL);
                array_push($candidates, ...$versions);
            }
            if ($programFiles !== '') {
                $candidates[] = $programFiles . '/Windows Defender/MpCmdRun.exe';
            }
            $binary = $this->firstExecutable($candidates);
        }

        if ($binary === '' || !is_file($binary) || !is_executable($binary)) {
            throw new RuntimeException('El antivirus local no está instalado o no se puede ejecutar.');
        }

        return [$driver, $binary];
    }

    /**
     * @param list<string> $candidates
     */
    private function firstExecutable(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return '';
    }
}
