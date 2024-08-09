<?php

namespace App\Services;

use App\Contracts\HttpContract;
use App\Contracts\MailParserContract;
use GuzzleHttp\Exception\GuzzleException;

class GuerrillaMailService implements MailParserContract
{
    private string $apiUrl;

    private readonly HttpContract $httpService;

    public function __construct(
    ) {
        $this->apiUrl = config('autoregister.mail_parser_api');
        $this->httpService = new GuzzleHttpService($this->apiUrl);
    }

    public function generateUniqueEmail()
    {
        $response = $this->httpService->get('/ajax.php', [
            'f' => 'get_email_address',
        ]);

        return json_decode($response, true);
    }

    public function getEmails(string $sidToken)
    {
        $response = $this->httpService->get('/ajax.php', [
            'f' => 'get_email_list',
            'offset' => 0,
            'sid_token' => $sidToken
        ]);

        return json_decode($response, true);
    }
}
