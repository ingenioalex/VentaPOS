<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $pages = config('seo.pages', []);
        $base = rtrim(config('app.url'), '/');

        return response()
            ->view('seo.sitemap', compact('pages', 'base'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $adminPath = trim((string) config('admin.panel_path', 'admin'), '/');

        return response()
            ->view('seo.robots', compact('base', 'adminPath'))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
