<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\SmartCaptchaException;
use Tigusigalpa\YandexSmartCaptcha\Laravel\Facades\SmartCaptcha;

/**
 * Custom validation rule for SmartCaptcha
 * 
 * Usage:
 * $request->validate([
 *     'smart-token' => ['required', new SmartCaptchaRule()],
 * ]);
 */
class SmartCaptchaRule implements Rule
{
    private ?string $errorMessage = null;

    /**
     * Determine if the validation rule passes
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            $this->errorMessage = 'Captcha token is required.';
            return false;
        }

        try {
            $result = SmartCaptcha::validate(
                $value,
                config('smartcaptcha.secret_key'),
                request()->ip()
            );

            if (!$result->isValid()) {
                $this->errorMessage = $result->message ?? 'Captcha validation failed.';
                
                Log::warning('SmartCaptcha validation failed', [
                    'status' => $result->status,
                    'message' => $result->message,
                    'ip' => request()->ip(),
                ]);
                
                return false;
            }

            Log::info('SmartCaptcha validation passed', [
                'host' => $result->host,
                'ip' => request()->ip(),
            ]);

            return true;
        } catch (SmartCaptchaException $e) {
            $this->errorMessage = 'Captcha validation error. Please try again.';
            
            Log::error('SmartCaptcha validation exception', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            
            return false;
        }
    }

    /**
     * Get the validation error message
     */
    public function message(): string
    {
        return $this->errorMessage ?? 'Please complete the captcha verification.';
    }
}
