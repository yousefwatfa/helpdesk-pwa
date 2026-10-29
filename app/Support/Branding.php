<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * The organisation's name, colour and logo: config/helpdesk.php defaults,
 * overridden by what an admin saved in Settings. Uploaded logos and the
 * icons generated from them live in public/branding (see IconGenerator).
 */
class Branding
{
    /** Folder under public/ for the uploaded logo and generated icons. */
    public static function directory(): string
    {
        return config('helpdesk.branding_directory', 'branding');
    }

    public function __construct(
        public string $name,
        public string $shortName,
        public string $organization,
        public string $primaryColor,
        public ?string $logo,
        public string $version,
    ) {
    }

    public static function load(): self
    {
        $defaults = config('helpdesk.brand');
        $saved = [];

        try {
            if (Schema::hasTable('settings')) {
                $saved = Setting::pluck('value', 'key')->all();
            }
        } catch (\Throwable $e) {
            // no database yet (fresh install): use the defaults
        }

        return new self(
            name: $saved['brand.name'] ?? $defaults['name'],
            shortName: $saved['brand.short_name'] ?? $defaults['short_name'],
            organization: $saved['brand.organization'] ?? $defaults['organization'],
            primaryColor: $saved['brand.primary_color'] ?? $defaults['primary_color'],
            logo: ($saved['brand.logo'] ?? null) ?: null,
            version: $saved['brand.version'] ?? '1',
        );
    }

    public function logoUrl(): string
    {
        return $this->logo
            ? asset(self::directory() . '/' . $this->logo) . '?v=' . $this->version
            : asset('icons/icon-192.png');
    }

    /** App icon (192 or 512, optionally maskable) generated from the logo, or the default icon. */
    public function iconUrl(int $size, bool $maskable = false): string
    {
        $file = $maskable ? "icon-maskable-{$size}.png" : "icon-{$size}.png";

        if ($this->logo && is_file(public_path(self::directory() . '/' . $file))) {
            return asset(self::directory() . '/' . $file) . '?v=' . $this->version;
        }

        return asset('icons/' . $file);
    }

    public function appleTouchIconUrl(): string
    {
        if ($this->logo && is_file(public_path(self::directory() . '/apple-touch-icon.png'))) {
            return asset(self::directory() . '/apple-touch-icon.png') . '?v=' . $this->version;
        }

        return asset('icons/apple-touch-icon.png');
    }

    /** "r, g, b" for Bootstrap's --bs-primary-rgb */
    public function primaryRgb(): string
    {
        return implode(', ', self::hexToRgb($this->primaryColor));
    }

    /** The primary colour a little darker, for hover/active states. */
    public function primaryActive(): string
    {
        [$r, $g, $b] = self::hexToRgb($this->primaryColor);

        return sprintf('#%02x%02x%02x', (int) ($r * 0.85), (int) ($g * 0.85), (int) ($b * 0.85));
    }

    /** @return array{int, int, int} */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
