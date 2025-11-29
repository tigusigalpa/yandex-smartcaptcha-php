<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tigusigalpa\YandexSmartCaptcha\DTO\CaptchaInfo;
use Tigusigalpa\YandexSmartCaptcha\DTO\SecretKey;
use Tigusigalpa\YandexSmartCaptcha\DTO\ValidationResult;

/**
 * @method static ValidationResult validate(string $token, string $secret, ?string $ip = null)
 * @method static CaptchaInfo createCaptcha(string $folderId, string $name, array $options = [])
 * @method static CaptchaInfo getCaptcha(string $captchaId)
 * @method static array listCaptchas(string $folderId, int $pageSize = 100, ?string $pageToken = null)
 * @method static CaptchaInfo updateCaptcha(string $captchaId, array $updates)
 * @method static array deleteCaptcha(string $captchaId)
 * @method static SecretKey getSecretKey(string $captchaId)
 * @method static \Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient setIamToken(string $iamToken)
 *
 * @see \Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient
 */
class SmartCaptcha extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return 'smartcaptcha';
    }
}
