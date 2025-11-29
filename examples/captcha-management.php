<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient;

/**
 * Captcha management example
 */

$oauthToken = 'your-oauth-token';
$folderId = 'your-folder-id';

// Initialize client with OAuth token
// The package will automatically exchange it for IAM token and refresh when needed
$client = new SmartCaptchaClient($oauthToken);

try {
    // Create new captcha
    echo "Creating captcha...\n";
    $captcha = $client->createCaptcha(
        folderId: $folderId,
        name: 'example-captcha',
        options: [
            'allowedSites' => ['example.com', 'www.example.com'],
            'complexity' => 'MEDIUM',
            'preCheckType' => 'CHECKBOX',
            'challengeType' => 'IMAGE_TEXT',
        ]
    );
    
    echo "✅ Captcha created!\n";
    echo "ID: {$captcha->id}\n";
    echo "Client Key: {$captcha->clientKey}\n";
    echo "Name: {$captcha->name}\n\n";
    
    $captchaId = $captcha->id;
    
    // Get captcha info
    echo "Getting captcha info...\n";
    $captcha = $client->getCaptcha($captchaId);
    echo "✅ Captcha info retrieved!\n";
    echo "Complexity: {$captcha->complexity}\n";
    echo "Created: {$captcha->createdAt}\n\n";
    
    // List captchas
    echo "Listing captchas...\n";
    $result = $client->listCaptchas($folderId, pageSize: 10);
    echo "✅ Found {count($result['captchas'])} captchas\n";
    foreach ($result['captchas'] as $c) {
        echo "- {$c->name} ({$c->id})\n";
    }
    echo "\n";
    
    // Get secret key
    echo "Getting secret key...\n";
    $secretKey = $client->getSecretKey($captchaId);
    echo "✅ Secret key: {$secretKey->serverKey}\n\n";
    
    // Update captcha
    echo "Updating captcha...\n";
    $captcha = $client->updateCaptcha($captchaId, [
        'name' => 'updated-example-captcha',
        'complexity' => 'HARD',
    ]);
    echo "✅ Captcha updated!\n";
    echo "New name: {$captcha->name}\n\n";
    
    // Delete captcha
    echo "Deleting captcha...\n";
    $operation = $client->deleteCaptcha($captchaId);
    echo "✅ Captcha deleted!\n";
    echo "Operation ID: {$operation['id']}\n";
    
} catch (\Tigusigalpa\YandexSmartCaptcha\Exceptions\SmartCaptchaException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
