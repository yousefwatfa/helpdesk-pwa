<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaController extends Controller
{
    /** Web app manifest built from the saved branding, so each organisation installs its own name and icon. */
    public function manifest(): JsonResponse
    {
        $brand = branding();

        return response()->json([
            'id' => '/',
            'name' => $brand->name,
            'short_name' => $brand->shortName,
            'description' => 'فتح ومتابعة تذاكر الدعم الفني',
            'lang' => 'ar',
            'dir' => 'rtl',
            'start_url' => '/dashboard?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => $brand->primaryColor,
            'icons' => [
                ['src' => $brand->iconUrl(192), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $brand->iconUrl(512), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $brand->iconUrl(512, maskable: true), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'تذكرة جديدة', 'url' => '/tickets/create', 'icons' => [['src' => $brand->iconUrl(192), 'sizes' => '192x192']]],
                ['name' => 'تذاكري', 'url' => '/dashboard', 'icons' => [['src' => $brand->iconUrl(192), 'sizes' => '192x192']]],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
