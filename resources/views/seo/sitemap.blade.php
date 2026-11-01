{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
    <url>
        <loc>{{ $base }}{{ $page['path'] }}</loc>
        <lastmod>{{ now()->toDateString() }}</lastmod>
        <changefreq>{{ $page['changefreq'] ?? 'monthly' }}</changefreq>
        <priority>{{ $page['priority'] ?? '0.5' }}</priority>
    </url>
@endforeach
</urlset>
