@php
    $seoTitle = trim($seoTitle ?? '');
    $seoDescription = trim($seoDescription ?? config('seo.default_description'));
    $seoPath = $seoPath ?? '/';
    $seoImage = $seoImage ?? asset('brand/logo.svg');
    $canonical = rtrim(config('app.url'), '/').($seoPath === '/' ? '' : $seoPath);
    if (! str_starts_with($seoPath, '/')) {
        $canonical = rtrim(config('app.url'), '/').'/'.ltrim($seoPath, '/');
    }
    $fullTitle = $seoTitle !== '' ? $seoTitle.' — '.config('seo.site_name') : config('seo.site_name');
@endphp
<link rel="canonical" href="{{ $canonical }}">
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="index, follow">
<meta name="geo.region" content="PE-LAM">
<meta name="geo.placename" content="Chiclayo">
<meta name="author" content="VentasÁgil">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:locale" content="{{ config('seo.locale', 'es_PE') }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if(!empty($jsonLd))
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
