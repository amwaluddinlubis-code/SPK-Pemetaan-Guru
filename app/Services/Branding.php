<?php

// Branding aplikasi (Redesign R0): warna aksen & logo pemda yang diatur
// dari halaman Pengaturan Pemda (kunci di tabel pengaturan) dan diterapkan
// ke seluruh aplikasi lewat variabel tema daisyUI yang disuntikkan layout.
// Mengganti warna/logo di pengaturan mengubah tampilan tanpa ubah kode.

namespace App\Services;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;

class Branding
{
    /** Warna aksen bawaan bila pemda belum mengatur (biru dinas). */
    public const WARNA_BAWAAN = '#2563eb';

    public const KUNCI_WARNA = 'branding_warna';

    public const KUNCI_LOGO = 'branding_logo';

    /** Warna aksen aktif (hex #rrggbb tervalidasi) atau warna bawaan. */
    public static function warna(): string
    {
        $warna = trim((string) Pengaturan::ambil(self::KUNCI_WARNA, ''));

        return self::hexValid($warna) ? strtolower($warna) : self::WARNA_BAWAAN;
    }

    public static function hexValid(string $warna): bool
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', trim($warna));
    }

    /** Path logo di disk public, atau null bila belum diatur/berkas hilang. */
    public static function logoPath(): ?string
    {
        $path = trim((string) Pengaturan::ambil(self::KUNCI_LOGO, ''));

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    public static function logoUrl(): ?string
    {
        $path = self::logoPath();

        return $path === null ? null : Storage::disk('public')->url($path);
    }

    /**
     * Warna teks yang kontras di atas warna latar $hex:
     * putih untuk warna gelap, abu sangat gelap untuk warna terang.
     */
    public static function kontras(string $hex): string
    {
        [$r, $g, $b] = self::rgb($hex);
        $luminansi = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminansi > 0.6 ? '#1f2937' : '#ffffff';
    }

    /**
     * Geser terang warna: $persen negatif menggelapkan (menuju hitam),
     * positif menerangkan (menuju putih). Rentang -100..100.
     */
    public static function ubahTerang(string $hex, float $persen): string
    {
        [$r, $g, $b] = self::rgb($hex);
        $persen = max(-100.0, min(100.0, $persen));

        if ($persen >= 0) {
            $r += (255 - $r) * $persen / 100;
            $g += (255 - $g) * $persen / 100;
            $b += (255 - $b) * $persen / 100;
        } else {
            $faktor = 1 + $persen / 100;
            $r *= $faktor;
            $g *= $faktor;
            $b *= $faktor;
        }

        return sprintf('#%02x%02x%02x', (int) round($r), (int) round($g), (int) round($b));
    }

    /**
     * CSS penimpa variabel tema daisyUI (primary/secondary/accent) untuk
     * tema terang & gelap, diturunkan dari warna aksen aktif. Disuntikkan
     * layout sebagai <style> setelah aset Vite.
     */
    public static function gayaTema(): string
    {
        $warna = self::warna();
        $sekunder = self::ubahTerang($warna, -16);
        // Tema gelap memakai warna aksen yang SAMA: mencerahkan aksen
        // (perilaku lama +18%) menurunkan kontras teks primer di bawah
        // ambang keterbacaan WCAG AA 4,5 — terukur 3,84 pada QA R6.
        $gelap = $warna;
        $sekunderGelap = self::ubahTerang($gelap, -10);

        return '[data-theme="light"]{'
            .'--color-primary:'.$warna.';'
            .'--color-primary-content:'.self::kontras($warna).';'
            .'--color-secondary:'.$sekunder.';'
            .'--color-secondary-content:'.self::kontras($sekunder).';'
            .'--color-accent:'.self::ubahTerang($warna, 22).';'
            .'}'
            .'[data-theme="dark"]{'
            .'--color-primary:'.$gelap.';'
            .'--color-primary-content:'.self::kontras($gelap).';'
            .'--color-secondary:'.$sekunderGelap.';'
            .'--color-secondary-content:'.self::kontras($sekunderGelap).';'
            .'--color-accent:'.self::ubahTerang($gelap, 20).';'
            .'}';
    }

    /** @return array{0:int, 1:int, 2:int} */
    private static function rgb(string $hex): array
    {
        $h = ltrim(trim($hex), '#');
        if (strlen($h) !== 6) {
            $h = ltrim(self::WARNA_BAWAAN, '#');
        }

        return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
    }
}
