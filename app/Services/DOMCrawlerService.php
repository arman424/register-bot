<?php

namespace App\Services;

use App\Contracts\DOMCrawlerContract;
use Symfony\Component\DomCrawler\Crawler;

class DOMCrawlerService implements DOMCrawlerContract
{
    public function parse(string $html, string $element, string $value): ?string
    {
        $crawler = new Crawler($html);

        return $crawler->filter($element)->attr($value);
    }
}
