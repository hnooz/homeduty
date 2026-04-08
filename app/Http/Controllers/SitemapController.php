<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('how-it-works'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => route('login'), 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['loc' => route('register'), 'changefreq' => 'yearly', 'priority' => '0.6'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>'.PHP_EOL;
            $xml .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'.PHP_EOL;
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'.PHP_EOL;
            $xml .= '    <priority>'.$url['priority'].'</priority>'.PHP_EOL;
            $xml .= '  </url>'.PHP_EOL;
        }

        $xml .= '</urlset>'.PHP_EOL;

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
