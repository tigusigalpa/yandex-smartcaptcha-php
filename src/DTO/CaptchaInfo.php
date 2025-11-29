<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\DTO;

/**
 * Captcha information DTO
 */
class CaptchaInfo
{
    public function __construct(
        public readonly string $id,
        public readonly string $folderId,
        public readonly string $cloudId,
        public readonly string $clientKey,
        public readonly string $createdAt,
        public readonly string $name,
        public readonly array $allowedSites,
        public readonly string $complexity,
        public readonly string $preCheckType,
        public readonly string $challengeType,
        public readonly ?array $securityRules = null,
        public readonly ?bool $deletionProtection = null,
        public readonly ?array $overrideVariants = null,
        public readonly ?bool $turnOffHostnameCheck = null,
        public readonly ?string $styleJson = null
    ) {
    }

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            folderId: $data['folderId'] ?? $data['folder_id'] ?? '',
            cloudId: $data['cloudId'] ?? $data['cloud_id'] ?? '',
            clientKey: $data['clientKey'] ?? $data['client_key'] ?? '',
            createdAt: $data['createdAt'] ?? $data['created_at'] ?? '',
            name: $data['name'] ?? '',
            allowedSites: $data['allowedSites'] ?? $data['allowed_sites'] ?? [],
            complexity: $data['complexity'] ?? 'MEDIUM',
            preCheckType: $data['preCheckType'] ?? $data['pre_check_type'] ?? 'CHECKBOX',
            challengeType: $data['challengeType'] ?? $data['challenge_type'] ?? 'IMAGE_TEXT',
            securityRules: $data['securityRules'] ?? $data['security_rules'] ?? null,
            deletionProtection: $data['deletionProtection'] ?? $data['deletion_protection'] ?? null,
            overrideVariants: $data['overrideVariants'] ?? $data['override_variants'] ?? null,
            turnOffHostnameCheck: $data['turnOffHostnameCheck'] ?? $data['turn_off_hostname_check'] ?? null,
            styleJson: $data['styleJson'] ?? $data['style_json'] ?? null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'folderId' => $this->folderId,
            'cloudId' => $this->cloudId,
            'clientKey' => $this->clientKey,
            'createdAt' => $this->createdAt,
            'name' => $this->name,
            'allowedSites' => $this->allowedSites,
            'complexity' => $this->complexity,
            'preCheckType' => $this->preCheckType,
            'challengeType' => $this->challengeType,
        ];

        if ($this->securityRules !== null) {
            $data['securityRules'] = $this->securityRules;
        }

        if ($this->deletionProtection !== null) {
            $data['deletionProtection'] = $this->deletionProtection;
        }

        if ($this->overrideVariants !== null) {
            $data['overrideVariants'] = $this->overrideVariants;
        }

        if ($this->turnOffHostnameCheck !== null) {
            $data['turnOffHostnameCheck'] = $this->turnOffHostnameCheck;
        }

        if ($this->styleJson !== null) {
            $data['styleJson'] = $this->styleJson;
        }

        return $data;
    }
}
