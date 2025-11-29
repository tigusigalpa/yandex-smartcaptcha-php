<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tigusigalpa\YandexCloudClient\Auth\IamTokenManager;
use Tigusigalpa\YandexSmartCaptcha\DTO\CaptchaInfo;
use Tigusigalpa\YandexSmartCaptcha\DTO\SecretKey;
use Tigusigalpa\YandexSmartCaptcha\DTO\ValidationResult;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\AuthenticationException;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\NotFoundException;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\RateLimitException;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\SmartCaptchaException;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\ValidationException;

/**
 * Main client for Yandex SmartCaptcha API
 */
class SmartCaptchaClient
{
    private const API_BASE_URI = 'https://smartcaptcha.api.cloud.yandex.net/';
    private const VALIDATION_URI = 'https://smartcaptcha.cloud.yandex.ru/validate';

    private ClientInterface $httpClient;
    private ClientInterface $validationClient;
    private ?IamTokenManager $tokenManager;
    private LoggerInterface $logger;

    /**
     * @param string|null $oauthToken OAuth token for API authentication (required for management API)
     * @param ClientInterface|null $httpClient Custom HTTP client
     * @param LoggerInterface|null $logger PSR-3 logger
     */
    public function __construct(
        ?string $oauthToken = null,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->tokenManager = $oauthToken ? new IamTokenManager($oauthToken) : null;
        $this->logger = $logger ?? new NullLogger();

        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => self::API_BASE_URI,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $this->validationClient = new Client([
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);
    }

    /**
     * Set OAuth token for API authentication
     */
    public function setOAuthToken(string $oauthToken): self
    {
        $this->tokenManager = new IamTokenManager($oauthToken);
        return $this;
    }

    /**
     * Set IamTokenManager directly
     */
    public function setTokenManager(IamTokenManager $tokenManager): self
    {
        $this->tokenManager = $tokenManager;
        return $this;
    }

    /**
     * Validate user token
     *
     * @param string $token User token from captcha
     * @param string $secret Server secret key
     * @param string|null $ip User IP address (optional but recommended)
     * @return ValidationResult
     * @throws SmartCaptchaException
     */
    public function validate(string $token, string $secret, ?string $ip = null): ValidationResult
    {
        try {
            $this->logger->info('Validating SmartCaptcha token', [
                'has_ip' => $ip !== null,
            ]);

            $formData = [
                'secret' => $secret,
                'token' => $token,
            ];

            if ($ip !== null) {
                $formData['ip'] = $ip;
            }

            $response = $this->validationClient->post(self::VALIDATION_URI, [
                'form_params' => $formData,
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new SmartCaptchaException('Invalid JSON response: ' . json_last_error_msg());
            }

            $result = ValidationResult::fromArray($data);

            $this->logger->info('SmartCaptcha validation result', [
                'status' => $result->status,
                'is_valid' => $result->isValid(),
            ]);

            return $result;
        } catch (BadResponseException $e) {
            $this->handleBadResponse($e);
        } catch (GuzzleException $e) {
            $this->logger->error('SmartCaptcha validation request failed', [
                'error' => $e->getMessage(),
            ]);
            throw new SmartCaptchaException('Validation request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create a new captcha
     *
     * @param string $folderId Yandex Cloud folder ID
     * @param string $name Captcha name
     * @param array $options Additional options
     * @return CaptchaInfo
     * @throws SmartCaptchaException
     */
    public function createCaptcha(string $folderId, string $name, array $options = []): CaptchaInfo
    {
        $this->ensureAuthenticated();

        $payload = array_merge([
            'folderId' => $folderId,
            'name' => $name,
        ], $options);

        $this->logger->info('Creating SmartCaptcha', [
            'folder_id' => $folderId,
            'name' => $name,
        ]);

        $data = $this->request('POST', 'smartcaptcha/v1/captchas', ['json' => $payload]);

        // Handle operation response
        if (isset($data['id']) && isset($data['done']) && !$data['done']) {
            // This is an operation, wait for completion or return operation ID
            $this->logger->info('SmartCaptcha creation operation started', [
                'operation_id' => $data['id'],
            ]);
        }

        // Extract captcha from response or metadata
        $captchaData = $data['response'] ?? $data['metadata'] ?? $data;

        return CaptchaInfo::fromArray($captchaData);
    }

    /**
     * Get captcha information
     *
     * @param string $captchaId Captcha ID
     * @return CaptchaInfo
     * @throws SmartCaptchaException
     */
    public function getCaptcha(string $captchaId): CaptchaInfo
    {
        $this->ensureAuthenticated();

        $this->logger->info('Getting SmartCaptcha info', [
            'captcha_id' => $captchaId,
        ]);

        $data = $this->request('GET', "smartcaptcha/v1/captchas/{$captchaId}");

        return CaptchaInfo::fromArray($data);
    }

    /**
     * List captchas in folder
     *
     * @param string $folderId Folder ID
     * @param int $pageSize Page size (default: 100)
     * @param string|null $pageToken Page token for pagination
     * @return array
     * @throws SmartCaptchaException
     */
    public function listCaptchas(string $folderId, int $pageSize = 100, ?string $pageToken = null): array
    {
        $this->ensureAuthenticated();

        $query = [
            'folderId' => $folderId,
            'pageSize' => $pageSize,
        ];

        if ($pageToken !== null) {
            $query['pageToken'] = $pageToken;
        }

        $this->logger->info('Listing SmartCaptchas', [
            'folder_id' => $folderId,
            'page_size' => $pageSize,
        ]);

        $data = $this->request('GET', 'smartcaptcha/v1/captchas', ['query' => $query]);

        $captchas = [];
        if (isset($data['captchas'])) {
            foreach ($data['captchas'] as $captchaData) {
                $captchas[] = CaptchaInfo::fromArray($captchaData);
            }
        }

        return [
            'captchas' => $captchas,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    /**
     * Update captcha
     *
     * @param string $captchaId Captcha ID
     * @param array $updates Update data
     * @return CaptchaInfo
     * @throws SmartCaptchaException
     */
    public function updateCaptcha(string $captchaId, array $updates): CaptchaInfo
    {
        $this->ensureAuthenticated();

        $this->logger->info('Updating SmartCaptcha', [
            'captcha_id' => $captchaId,
        ]);

        $data = $this->request('PATCH', "smartcaptcha/v1/captchas/{$captchaId}", ['json' => $updates]);

        // Handle operation response
        $captchaData = $data['response'] ?? $data['metadata'] ?? $data;

        return CaptchaInfo::fromArray($captchaData);
    }

    /**
     * Delete captcha
     *
     * @param string $captchaId Captcha ID
     * @return array Operation info
     * @throws SmartCaptchaException
     */
    public function deleteCaptcha(string $captchaId): array
    {
        $this->ensureAuthenticated();

        $this->logger->info('Deleting SmartCaptcha', [
            'captcha_id' => $captchaId,
        ]);

        return $this->request('DELETE', "smartcaptcha/v1/captchas/{$captchaId}");
    }

    /**
     * Get secret key for captcha
     *
     * @param string $captchaId Captcha ID
     * @return SecretKey
     * @throws SmartCaptchaException
     */
    public function getSecretKey(string $captchaId): SecretKey
    {
        $this->ensureAuthenticated();

        $this->logger->info('Getting SmartCaptcha secret key', [
            'captcha_id' => $captchaId,
        ]);

        $data = $this->request('GET', "smartcaptcha/v1/captchas/{$captchaId}:getSecretKey");

        return SecretKey::fromArray($data);
    }

    /**
     * Make HTTP request to API
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param array $options Request options
     * @return array Response data
     * @throws SmartCaptchaException
     */
    private function request(string $method, string $path, array $options = []): array
    {
        try {
            if ($this->tokenManager !== null) {
                $options['headers']['Authorization'] = 'Bearer ' . $this->tokenManager->getValidIamToken();
            }

            $response = $this->httpClient->request($method, $path, $options);
            $body = (string) $response->getBody();

            if (empty($body)) {
                return [];
            }

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new SmartCaptchaException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (BadResponseException $e) {
            $this->handleBadResponse($e);
        } catch (GuzzleException $e) {
            $this->logger->error('SmartCaptcha API request failed', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw new SmartCaptchaException('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle bad HTTP response
     *
     * @param BadResponseException $e
     * @throws SmartCaptchaException
     */
    private function handleBadResponse(BadResponseException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = (string) $e->getResponse()->getBody();

        $this->logger->error('SmartCaptcha API error', [
            'status_code' => $statusCode,
            'body' => $body,
        ]);

        $errorMessage = $body;
        $data = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
            $errorMessage = $data['message'];
        }

        switch ($statusCode) {
            case 400:
                throw new ValidationException('Validation error: ' . $errorMessage, $statusCode, $e);
            case 401:
            case 403:
                throw new AuthenticationException('Authentication failed: ' . $errorMessage, $statusCode, $e);
            case 404:
                throw new NotFoundException('Resource not found: ' . $errorMessage, $statusCode, $e);
            case 429:
                throw new RateLimitException('Rate limit exceeded: ' . $errorMessage, $statusCode, $e);
            default:
                throw new SmartCaptchaException('API error: ' . $errorMessage, $statusCode, $e);
        }
    }

    /**
     * Ensure client is authenticated
     *
     * @throws AuthenticationException
     */
    private function ensureAuthenticated(): void
    {
        if ($this->tokenManager === null) {
            throw new AuthenticationException('OAuth token is required for this operation. Please provide OAuth token in constructor or use setOAuthToken() method.');
        }
    }
}
