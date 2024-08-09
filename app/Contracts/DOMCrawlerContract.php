<?php

namespace App\Contracts;

interface DOMCrawlerContract
{
    public function parse(string $html, string $element, string $value): ?string;
}
