<?php

declare(strict_types=1);

namespace Tests\Feature;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

final class TabnabbingProtectionTest extends TestCase
{
    public function test_login_and_unauthenticated_admin_responses_isolate_window_openers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $this->get('/admin')
            ->assertRedirect()
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_unsafe_new_window_targets_are_rejected(): void
    {
        $this->assertFalse(self::safeTargetTag('<a href="https://example.test" target="_blank">Example</a>'));
        $this->assertFalse(self::safeTargetTag('<a target="_blank" rel="noreferrer">Example</a>'));
        $this->assertFalse(self::safeTargetTag('<a target="namedWindow" rel="noopener noreferrer">Example</a>'));
        $this->assertFalse(self::safeTargetTag('<form target="_blank" action="/admin">'));
        $this->assertFalse(self::safeTargetTag('<button formtarget="_blank">Submit</button>'));
        $this->assertTrue(self::safeTargetTag('<a href="/admin">Admin</a>'));
        $this->assertTrue(self::safeTargetTag('<a target="_blank" rel="noreferrer noopener" href="https://example.test">Example</a>'));
    }

    public function test_server_rendered_markup_does_not_create_unprotected_opener_relationships(): void
    {
        foreach (self::sourceFiles(['resources/views', 'app'], ['php', 'html']) as $file) {
            $source = (string) file_get_contents($file->getPathname());
            $count = preg_match_all('~<(?:a|area|form|button|input)\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>~is', $source, $matches);
            $this->assertNotFalse($count, $file->getPathname());

            foreach ($matches[0] as $tag) {
                $this->assertTrue(
                    self::safeTargetTag($tag),
                    $file->getPathname() . ': unsafe window target in ' . $tag,
                );
            }
        }
    }

    public function test_application_scripts_cannot_create_unreviewed_new_windows(): void
    {
        // New popup features require explicit security review. For now the
        // application navigates in the current tab, so these APIs are forbidden.
        $unsafeOpeners = '~\b(?:window|self|globalThis|top|parent)\s*(?:\.\s*open|\[\s*["\']open["\']\s*\])\s*\(|\b_blank\b|\b(?:window|self|globalThis)\s*\.\s*opener\b~i';

        foreach (self::sourceFiles(['resources/ts', 'public/js', 'public/admin/js', 'resources/views', 'app'], ['ts', 'js', 'mjs', 'php']) as $file) {
            $source = (string) file_get_contents($file->getPathname());
            $this->assertDoesNotMatchRegularExpression(
                $unsafeOpeners,
                $source,
                $file->getPathname() . ': review popup creation and use an opener-isolating mechanism.',
            );
        }
    }

    private static function safeTargetTag(string $tag): bool
    {
        if (!preg_match('~^<\s*(a|area|form|button|input)\b~i', $tag, $tagName)) {
            return false;
        }

        foreach (['target', 'formtarget'] as $name) {
            $target = self::attribute($tag, $name);
            if ($target === null) {
                continue;
            }

            $target = strtolower(trim($target));
            if (in_array($target, ['_self', '_parent', '_top'], true)) {
                continue;
            }

            if ($target === '_blank' && in_array(strtolower($tagName[1]), ['a', 'area'], true)) {
                $rel = self::attribute($tag, 'rel') ?? '';
                if (preg_match('~(?:^|\s)noopener(?:\s|$)~i', $rel)
                    && preg_match('~(?:^|\s)noreferrer(?:\s|$)~i', $rel)) {
                    continue;
                }
            }

            // Named browsing contexts and dynamic target values are rejected:
            // they cannot be safely assessed with a purely static check.
            return false;
        }

        return true;
    }

    private static function attribute(string $tag, string $name): ?string
    {
        $pattern = '~(?:^|\s)' . preg_quote($name, '~') . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))~i';
        if (!preg_match($pattern, $tag, $matches)) {
            return null;
        }

        return ($matches[1] ?? '') !== '' ? $matches[1] : ((($matches[2] ?? '') !== '') ? $matches[2] : ($matches[3] ?? ''));
    }

    /** @param list<string> $directories
     *  @param list<string> $extensions
     *  @return list<SplFileInfo>
     */
    private static function sourceFiles(array $directories, array $extensions): array
    {
        $files = [];
        foreach ($directories as $directory) {
            $root = base_path($directory);
            if (!is_dir($root)) {
                throw new \RuntimeException('Missing audited source directory: ' . $root);
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            );
            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile()
                    && in_array(strtolower($file->getExtension()), $extensions, true)) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}
