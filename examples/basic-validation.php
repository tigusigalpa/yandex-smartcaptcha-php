<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient;

/**
 * Basic token validation example
 */

// Initialize client (IAM token not required for validation)
$client = new SmartCaptchaClient();

// Get token from POST request
$token = $_POST['smart-token'] ?? '';
$secret = 'your-server-secret-key';
$userIp = $_SERVER['REMOTE_ADDR'] ?? null;

if (empty($token)) {
    die('Token is required');
}

try {
    // Validate token
    $result = $client->validate($token, $secret, $userIp);
    
    if ($result->isValid()) {
        echo "✅ Validation successful!\n";
        echo "Status: {$result->status}\n";
        echo "Host: {$result->host}\n";
        
        // Process form data
        // ...
    } else {
        echo "❌ Validation failed!\n";
        echo "Status: {$result->status}\n";
        echo "Message: {$result->message}\n";
    }
} catch (\Tigusigalpa\YandexSmartCaptcha\Exceptions\SmartCaptchaException $e) {
    echo "Error: {$e->getMessage()}\n";
}
