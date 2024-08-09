<?php

namespace App\Modules;

use App\Contracts\CaptchaSolverContract;
use App\Contracts\DOMCrawlerContract;
use App\Contracts\HttpContract;
use App\Contracts\MailParserContract;
use App\Contracts\ModulesContract;
use App\Services\GuzzleHttpService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class BlackScaleModule implements ModulesContract
{
    private array $requestHeaders;

    private HttpContract $httpService;

    public function __construct(
        private readonly MailParserContract $mailParserService,
        private readonly DOMCrawlerContract $DOMCrawlerService,
        private readonly CaptchaSolverContract $captchaSolverService,
    ) {
        $requestBaseUrl = config('autoregister.remote_base_url');

        $this->httpService = new GuzzleHttpService($requestBaseUrl);
        $this->requestHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Referer' => $requestBaseUrl,
        ];
    }

    public function register(): string
    {
        try {
            // Get the CSRF token from the cookies
            $cookies = $this->httpService->getCookies();
            $csrfToken = $this->getCSRFToken($cookies);
            $this->httpService->setCookie($csrfToken, config('autoregister.remote_domain'));

            // Generate a random email
            $emailData = $this->mailParserService->generateUniqueEmail();

            // Prepare the registration data
            $registrationData = $this->prepareRegisterData($emailData['email_addr']);
            // Register Step 1
            $this->passStep($registrationData, '/verify.php');

            // Get emails from the email address
            $verificationCode = $this->getEmails($emailData['sid_token']);
            // Register Step 2 - email verification
            $captchaPage = $this->passStep(['code' => $verificationCode], '/captcha.php');

            // Retrieve the site key of the captcha
            $siteKey = $this->DOMCrawlerService->parse($captchaPage, 'div.g-recaptcha', 'data-sitekey');
            // Create a task for solving the captcha
            $taskId = $this->captchaSolverService->createRecaptchaV2Task(
                $siteKey,
                config('autoregister.remote_base_url') . '/captcha.php'
            );
            $captchaSolution = $this->captchaSolverService->getResult($taskId);

            // Register Step 3 - captcha
            return $this->passStep(['g-recaptcha-response' => $captchaSolution], '/complete.php');
        } catch (Exception $e) {
            Log::error($e->getMessage());
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }

        return "Something went wrong.";
    }

    private function getCSRFToken(array $cookies): ?string
    {
        $csrfToken = null;

        foreach ($cookies as $cookie) {
            if ($cookie['Name'] === 'ctoken') {
                $csrfToken = $cookie['Value'];
                break;
            }
        }

        return $csrfToken;
    }

    private function prepareRegisterData(string $email): array
    {
        // Get the registration page in order to parse the hidden input values
        $registerPage = $this->httpService->get('/register.php');
        $stoken = $this->DOMCrawlerService->parse($registerPage, 'input[name="stoken"]', 'value');

        $prefix = strstr($email, '@', true);
        $email = $prefix . config('autoregister.mail_parser_domain');

        return [
            'stoken' => $stoken,
            'fullname' => 'test',
            'email' => $email,
            'password' => 'qwe',
            'email_signature' => base64_encode($email),
        ];
    }

    /**
     * @throws Exception
     */
    private function getEmails(string $sidToken): string
    {
        $maxAttempts = 5;
        $delay = 15;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $emails = $this->mailParserService->getEmails($sidToken);

            $verificationCode = $this->checkEmailForVerificationCode($emails['list'][0]);

            if ($verificationCode) {
                return $verificationCode;
            }

            sleep($delay);
        }

        throw new Exception('Verification Email has not been delivered');
    }

    private function checkEmailForVerificationCode($data): ?string
    {
        preg_match('/Your verification code is: (\w+)/', $data['mail_excerpt'], $matches);

        if ($matches) {
            return $matches[1];
        }

        return null;
    }

    private function passStep(array $requestData, string $uri): string
    {
        return $this->httpService->post($uri, [
            'form_params' => $requestData
        ], $this->requestHeaders);
    }
}
