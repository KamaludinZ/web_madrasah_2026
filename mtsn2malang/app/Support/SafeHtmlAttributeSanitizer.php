<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Menyaring nilai atribut yang tidak diperiksa oleh sanitizer bawaan:
 * - style: hanya properti tampilan teks yang aman (tanpa position/url()/dsb.),
 *   agar konten tidak bisa menutupi halaman (mis. position:fixed).
 * - class: buang kelas utilitas yang bisa dipakai untuk menutupi/menyembunyikan
 *   bagian halaman (fixed, absolute, z-*, inset-*, hidden, dsb.).
 * - iframe src: hanya embed dari layanan video/peta yang dikenal.
 */
class SafeHtmlAttributeSanitizer implements AttributeSanitizerInterface
{
    private const ALLOWED_STYLE_PROPERTIES = [
        'text-align', 'color', 'background-color', 'font-weight', 'font-style',
        'text-decoration', 'text-decoration-line', 'vertical-align', 'width',
        'height', 'max-width', 'min-width', 'list-style-type', 'text-indent',
        'border', 'border-collapse', 'padding', 'margin', 'margin-left', 'margin-right',
    ];

    private const BLOCKED_CLASS_PATTERN = '/^(fixed|absolute|sticky|hidden|invisible|opacity-\S*|-?z-\S*|-?inset\S*|-?top-\S*|-?bottom-\S*|-?left-\S*|-?right-\S*|w-screen|h-screen)$/';

    private const ALLOWED_IFRAME_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com',
        'player.vimeo.com', 'www.google.com', 'maps.google.com', 'drive.google.com',
        'docs.google.com', 'www.instagram.com', 'www.facebook.com', 'www.tiktok.com',
    ];

    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style', 'class', 'src'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        return match ($attribute) {
            'style' => $this->sanitizeStyle($value),
            'class' => $this->sanitizeClass($value),
            'src' => $element === 'iframe' ? $this->sanitizeIframeSrc($value) : $value,
            default => $value,
        };
    }

    private function sanitizeStyle(string $value): ?string
    {
        $safe = [];

        foreach (explode(';', $value) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $propertyValue] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            if (! in_array($property, self::ALLOWED_STYLE_PROPERTIES, true)) {
                continue;
            }

            if ($propertyValue === '' || preg_match('/url\s*\(|expression|javascript:|[<>\\\\]/i', $propertyValue)) {
                continue;
            }

            $safe[] = $property . ': ' . $propertyValue;
        }

        return $safe === [] ? null : implode('; ', $safe);
    }

    private function sanitizeClass(string $value): ?string
    {
        $classes = array_filter(
            preg_split('/\s+/', trim($value)) ?: [],
            fn (string $class) => $class !== '' && ! preg_match(self::BLOCKED_CLASS_PATTERN, $class)
        );

        return $classes === [] ? null : implode(' ', $classes);
    }

    private function sanitizeIframeSrc(string $value): ?string
    {
        $parts = parse_url(trim($value));

        if (($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        return in_array(strtolower($parts['host'] ?? ''), self::ALLOWED_IFRAME_HOSTS, true) ? $value : null;
    }
}
