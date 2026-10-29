<?php

namespace App\Support;

/**
 * Builds the PWA icons (192, 512, maskable 512, apple-touch 180) with GD:
 * from an uploaded logo, or the default ticket icon in a given colour.
 */
class IconGenerator
{
    /** Icons from a logo: the logo centred on white, with extra padding for the maskable one. */
    public static function fromLogo(string $logoPath, string $directory): void
    {
        $source = @imagecreatefromstring((string) file_get_contents($logoPath));
        if (!$source) {
            throw new \RuntimeException('Unsupported image.');
        }

        foreach (self::targets() as $file => [$size, $padding]) {
            $canvas = self::canvas($size, [255, 255, 255]);
            self::placeCentred($canvas, $source, $size, $padding);
            imagepng($canvas, $directory . '/' . $file);
            imagedestroy($canvas);
        }

        imagedestroy($source);
    }

    /** The default icon: a white ticket on the primary colour. */
    public static function defaultIcons(string $hexColor, string $directory): void
    {
        foreach (self::targets() as $file => [$size, $padding]) {
            $canvas = self::canvas($size, Branding::hexToRgb($hexColor));
            self::drawTicket($canvas, $size, $padding, Branding::hexToRgb($hexColor));
            imagepng($canvas, $directory . '/' . $file);
            imagedestroy($canvas);
        }
    }

    /** @return array<string, array{int, float}> file => [size, padding ratio] */
    private static function targets(): array
    {
        return [
            'icon-192.png' => [192, 0.12],
            'icon-512.png' => [512, 0.12],
            'icon-maskable-512.png' => [512, 0.24], // safe zone for round/squircle masks
            'apple-touch-icon.png' => [180, 0.14],
        ];
    }

    private static function canvas(int $size, array $rgb): \GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...$rgb));

        return $canvas;
    }

    private static function placeCentred(\GdImage $canvas, \GdImage $source, int $size, float $padding): void
    {
        $box = (int) round($size * (1 - 2 * $padding));
        $w = imagesx($source);
        $h = imagesy($source);
        $scale = min($box / $w, $box / $h);
        $tw = max(1, (int) round($w * $scale));
        $th = max(1, (int) round($h * $scale));

        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), 0, 0, $tw, $th, $w, $h);
    }

    private static function drawTicket(\GdImage $canvas, int $size, float $padding, array $bg): void
    {
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $hole = imagecolorallocate($canvas, ...$bg);

        $inset = $size * ($padding + 0.06);
        $x1 = (int) $inset;
        $x2 = (int) ($size - $inset);
        $h = ($x2 - $x1) * 0.62;
        $y1 = (int) (($size - $h) / 2);
        $y2 = (int) ($y1 + $h);
        $r = (int) ($h * 0.125);

        // rounded rectangle
        imagefilledrectangle($canvas, $x1 + $r, $y1, $x2 - $r, $y2, $white);
        imagefilledrectangle($canvas, $x1, $y1 + $r, $x2, $y2 - $r, $white);
        foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$cx, $cy]) {
            imagefilledellipse($canvas, $cx, $cy, 2 * $r, 2 * $r, $white);
        }

        // notches on both sides
        $notch = (int) ($h * 0.32);
        $midY = (int) (($y1 + $y2) / 2);
        imagefilledellipse($canvas, $x1, $midY, $notch, $notch, $hole);
        imagefilledellipse($canvas, $x2, $midY, $notch, $notch, $hole);

        // dashed tear line
        $lineX = (int) ($x1 + ($x2 - $x1) * 0.68);
        $dash = max(2, (int) ($h * 0.063));
        $thick = max(1, (int) ($h * 0.035));
        for ($y = $y1 + $dash; $y < $y2 - $dash; $y += 2 * $dash) {
            imagefilledrectangle($canvas, $lineX - $thick, $y, $lineX + $thick, min($y + $dash, $y2 - $dash), $hole);
        }

        // three text lines on the left part
        $lx1 = (int) ($x1 + ($x2 - $x1) * 0.14);
        $lx2 = (int) ($x1 + ($x2 - $x1) * 0.56);
        $lh = max(1, (int) ($h * 0.07));
        foreach ([0.30, 0.50, 0.70] as $i => $ratio) {
            $ly = (int) ($y1 + $h * $ratio);
            $end = $i === 2 ? (int) ($lx1 + ($lx2 - $lx1) * 0.6) : $lx2;
            imagefilledrectangle($canvas, $lx1, $ly - $lh, $end, $ly + $lh, $hole);
        }
    }
}
