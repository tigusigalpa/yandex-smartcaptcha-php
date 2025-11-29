<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\DTO;

/**
 * Secret key DTO
 */
class SecretKey
{
    public function __construct(
        public readonly string $serverKey
    ) {
    }

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            serverKey: $data['serverKey'] ?? $data['server_key'] ?? ''
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'serverKey' => $this->serverKey,
        ];
    }
}
