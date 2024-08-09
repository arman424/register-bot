<?php

namespace App\Services;

use App\Contracts\CaptchaSolverContract;
use App\Contracts\HttpContract;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class NextCaptchaService implements CaptchaSolverContract
{
    private string $apiUrl;

    private string $clientKey;

    private readonly HttpContract $httpService;

    public function __construct()
    {
        $this->apiUrl = config('autoregister.next_captcha_solver_api');
        $this->clientKey = config('autoregister.next_captcha_solver_api_key');
        $this->httpService = new GuzzleHttpService($this->apiUrl);
    }

    /**
     * @throws Exception
     */
    public function createRecaptchaV2Task(string $siteKey, string $websiteUrl)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = json_encode([
            'clientKey' => $this->clientKey,
            'task' => [
                'type' => 'RecaptchaV2TaskProxyless',
                'websiteURL' => $websiteUrl,
                'websiteKey' => $siteKey,
            ],
        ]);

        try {
            $response = $this->httpService->post('/createTask', [
                'body' => $body,
            ], $headers);

            $result = json_decode($response, true);

            if ($result['errorId'] === 0) {
                return $result['taskId'];
            } else {
                throw new Exception("Error creating task: {$result['errorDescription']}\n");
            }
        } catch (RequestException $e) {
            Log::error("Request failed: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getResult($taskId)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = json_encode([
            'clientKey' => $this->clientKey,
            'taskId' => $taskId,
        ]);

        do {
            try {
                $response = $this->httpService->post('/getTaskResult', [
                    'body' => $body,
                ], $headers);

                $result = json_decode($response, true);

                if ($result['errorId'] === 0) {
                    if ($result['status'] === 'ready') {
                        return $result['solution']['gRecaptchaResponse'];
                    } elseif ($result['status'] === 'processing') {
                        sleep(10);
                    }
                } else {
                    throw new Exception("Error getting result: {$result['errorDescription']}\n");
                }
            } catch (RequestException $e) {
                Log::error("Request failed: " . $e->getMessage());
            }
        } while (true);
    }
}
