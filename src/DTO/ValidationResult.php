<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\DTO;

/**
 * Validation result DTO
 */
class ValidationResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $message = null,
        public readonly ?string $host = null
    ) {
    }

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 'failed',
            message: $data['message'] ?? null,
            host: $data['host'] ?? null
        );
    }

    /**
     * Check if validation passed
     */
    public function isValid(): bool
    {
        return $this->status === 'ok';
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $data = ['status' => $this->status];

        if ($this->message !== null) {
            $data['message'] = $this->message;
        }

        if ($this->host !== null) {
            $data['host'] = $this->host;
        }

        return $data;
    }
}
