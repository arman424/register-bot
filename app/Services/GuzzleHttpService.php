<?php

namespace App\Services;

use App\Contracts\HttpContract;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GuzzleHttpService implements HttpContract
{
    private string $baseUri;

    private Client $client;

    private CookieJar $cookieJar;

    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
        $this->cookieJar = new CookieJar();
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'cookies' => $this->cookieJar
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getCookies(): array
    {
        $this->client->request('GET', $this->baseUri, [
            'cookies' => $this->cookieJar,
        ]);

        return $this->cookieJar->toArray();
    }

    public function setCookie(string $csrfToken, string $domain = ''): void
    {
        $this->cookieJar->setCookie(new SetCookie([
            'Name' => 'ctoken',
            'Value' => $csrfToken,
            'Domain' => $domain,
            'Path' => '/',
            'Secure' => true,
            'HttpOnly' => true,
        ]));
    }

    public function get(string $uri, array $query = [])
    {
        try {
            $response = $this->client->request('GET', $uri, [
                'cookies' => $this->cookieJar,
                'query' => $query
            ]);

            return $response->getBody();
        } catch (RequestException $e) {
            Log::error($e->getMessage());
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }
    }

    public function post(string $uri, array $data = [], array $headers = [])
    {
        try {
            $requestData = [
                'headers' => $headers,
                'cookies' => $this->cookieJar,
            ] + $data;

            $response = $this->client->request('POST', $uri, $requestData);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            Log::error($e->getMessage());
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }
    }
}
