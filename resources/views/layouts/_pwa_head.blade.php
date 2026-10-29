{{-- PWA: installable app metadata + brand colour (shared by app and auth layouts) --}}
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="{{ $brand->primaryColor }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $brand->shortName }}">
<link rel="icon" type="image/png" href="{{ $brand->iconUrl(192) }}">
<link rel="apple-touch-icon" href="{{ $brand->appleTouchIconUrl() }}">
<style>
    /* html:root beats the theme's :root and the later mobile.css :root */
    html:root, html[data-bs-theme=light] {
        --bs-primary: {{ $brand->primaryColor }};
        --bs-primary-rgb: {{ $brand->primaryRgb() }};
        --bs-primary-active: {{ $brand->primaryActive() }};
        --bs-link-color: {{ $brand->primaryColor }};
        --bs-link-hover-color: {{ $brand->primaryActive() }};
        --bs-text-primary: {{ $brand->primaryColor }};
        --bs-component-active-bg: {{ $brand->primaryColor }};
        --bs-component-hover-color: {{ $brand->primaryColor }};
        /* mobile.css / app.css */
        --m-primary: {{ $brand->primaryColor }};
        --m-primary-strong: {{ $brand->primaryActive() }};
        --color-primary: {{ str_replace(',', '', $brand->primaryRgb()) }};
    }
    .btn.btn-primary { --bs-btn-bg: {{ $brand->primaryColor }}; --bs-btn-border-color: {{ $brand->primaryColor }}; }
</style>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function (err) {
                console.warn('Service worker registration failed', err);
            });
        });
    }
</script>
