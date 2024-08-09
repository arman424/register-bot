<?php

namespace App\Contracts;

interface HttpContract
{
    public function get(string $uri, array $query = []);

    public function post(string $uri, array $data = [], array $headers = []);
}
