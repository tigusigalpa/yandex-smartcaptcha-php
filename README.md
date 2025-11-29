# Yandex SmartCaptcha PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)

PHP SDK for [Yandex SmartCaptcha](https://yandex.cloud/en/services/smartcaptcha) with full Laravel support.

[Русская версия](README-ru.md)

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started with Yandex SmartCaptcha](#getting-started-with-yandex-smartcaptcha)
  - [Prerequisites](#prerequisites)
  - [Create Yandex Cloud Account](#1-create-yandex-cloud-account)
  - [Create Captcha in Console](#2-create-captcha-in-console)
  - [Get Captcha Keys](#3-get-captcha-keys)
  - [Get IAM Token](#4-get-iam-token-for-api-management)
- [Quick Start](#quick-start)
- [API Methods](#api-methods)
- [Frontend Integration](#frontend-integration)
- [Laravel Validation Rule](#laravel-validation-rule)
- [Error Handling](#error-handling)
- [Logging](#logging)
- [Testing](#testing)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [Support](#support)

## Features

- ✅ **Complete API Coverage**: All SmartCaptcha API methods
- 🔐 **Token Validation**: Validate user captcha tokens
- 📦 **Captcha Management**: Create, update, delete, and list captchas
- 🔑 **Secret Key Retrieval**: Get server secret keys
- 🎨 **Laravel Integration**: Service provider, facade, and config
- 📝 **Type Safety**: Full PHP 8.0+ type hints and strict types
- 🪵 **PSR-3 Logging**: Optional logging support
- 🧪 **Well Tested**: Comprehensive test coverage
- 📖 **Well Documented**: Detailed documentation and examples

## Requirements

- PHP 8.0 or higher
- Guzzle HTTP client 7.0+
- Laravel 8.0+ (optional, for Laravel integration)

## Installation

Install via Composer:

```bash
composer require tigusigalpa/yandex-smartcaptcha-php
```

## Getting Started with Yandex SmartCaptcha

### Prerequisites

Before you begin, you need to set up Yandex Cloud and create a captcha:

#### 1. Create Yandex Cloud Account

1. Go to [Yandex Cloud Console](https://console.yandex.cloud)
2. Sign in to Yandex Cloud or register if you haven't already
3. On the [Yandex Cloud Billing](https://center.yandex.cloud/billing/accounts) page, make sure you have a billing account linked and it has the `ACTIVE` or `TRIAL_ACTIVE` status
4. If you don't have a billing account, [create one](https://yandex.cloud/en/docs/billing/quickstart/)

#### 2. Create Captcha in Console

1. In the [management console](https://console.yandex.cloud), select your folder
2. Select **Yandex SmartCaptcha** service
3. Click **Create captcha**
4. Enter a captcha name. Naming requirements:
   - Length: 2 to 63 characters
   - Can contain lowercase Latin letters, numbers, and hyphens
   - First character must be a letter, last character cannot be a hyphen
5. (Optional) Disable domain name verification if needed
6. Specify the list of sites where the captcha will be placed (e.g., `example.com`)
7. Leave the appearance as standard
8. Configure the default captcha:
   - Select the **pre-check type** (checkbox or slider)
   - Select the **challenge type** (image-text task)
   - Choose **complexity**: Easy, Medium, or Hard
9. (Optional) Enable or disable the use of HTTP request information for ML model training
10. Click **Create**

#### 3. Get Captcha Keys

After creating the captcha:

1. In the [management console](https://console.yandex.cloud), select your folder
2. Select **Yandex SmartCaptcha** service
3. Click on the captcha name
4. On the **Overview** tab, copy:
   - **Client Key** (for frontend widget)
   - **Server Key** (for backend validation)

#### 4. Get OAuth Token (for API management)

To manage captchas via API, you need an OAuth token. The package will automatically exchange it for an IAM token and refresh it when needed.

**Get OAuth Token:**

Visit the following URL and authorize the application:

```
https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb
```

After authorization, you'll receive an OAuth token. Copy it and use it in your application.

**Note:** The package uses `tigusigalpa/yandex-cloud-client-php` which handles:
- Automatic OAuth to IAM token exchange
- IAM token caching (valid for 12 hours)
- Automatic token refresh when expired

Now you have:
- ✅ Client Key (for frontend)
- ✅ Server Secret Key (for validation)
- ✅ OAuth Token (for API management - will be auto-exchanged to IAM)
- ✅ Folder ID (from console)

## Quick Start

### Basic Usage (Pure PHP)

```php
use Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient;

// Create client with OAuth token
// The package will automatically exchange it for IAM token and refresh when needed
$client = new SmartCaptchaClient($oauthToken);

// Validate user token
$result = $client->validate(
    token: $_POST['smart-token'],
    secret: 'your-server-secret-key',
    ip: $_SERVER['REMOTE_ADDR']
);

if ($result->isValid()) {
    echo "✅ Human verified!";
} else {
    echo "❌ Bot detected!";
}
```

### Laravel Usage

#### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=smartcaptcha-config
```

#### 2. Configure Environment

Add to your `.env`:

```env
YANDEX_SMARTCAPTCHA_OAUTH_TOKEN=your-oauth-token
YANDEX_SMARTCAPTCHA_SECRET_KEY=your-secret-key
YANDEX_SMARTCAPTCHA_CLIENT_KEY=your-client-key
YANDEX_SMARTCAPTCHA_FOLDER_ID=your-folder-id
```

**Note:** The package uses `yandex-cloud-client-php` for authentication. Your OAuth token will be automatically exchanged for an IAM token and refreshed when needed (IAM tokens expire after 12 hours).

#### 3. Use Facade

```php
use Tigusigalpa\YandexSmartCaptcha\Laravel\Facades\SmartCaptcha;

// Validate token
$result = SmartCaptcha::validate(
    request()->input('smart-token'),
    config('smartcaptcha.secret_key'),
    request()->ip()
);

if ($result->isValid()) {
    // User is verified
}
```

## API Methods

### Token Validation

Validate user captcha token:

```php
$result = $client->validate(
    token: 'user-token',
    secret: 'server-secret-key',
    ip: '192.168.1.1' // optional but recommended
);

// Check result
if ($result->isValid()) {
    echo "Status: {$result->status}";
    echo "Host: {$result->host}";
}
```

### Create Captcha

Create a new captcha:

```php
$captcha = $client->createCaptcha(
    folderId: 'b1g0ijbfaqsn12345678',
    name: 'my-captcha',
    options: [
        'allowedSites' => ['example.com'],
        'complexity' => 'MEDIUM', // EASY, MEDIUM, HARD
        'preCheckType' => 'CHECKBOX', // CHECKBOX, SLIDER
        'challengeType' => 'IMAGE_TEXT',
    ]
);

echo "Captcha ID: {$captcha->id}";
echo "Client Key: {$captcha->clientKey}";
```

### Get Captcha

Get captcha information:

```php
$captcha = $client->getCaptcha('captcha-id');

echo "Name: {$captcha->name}";
echo "Complexity: {$captcha->complexity}";
echo "Created: {$captcha->createdAt}";
```

### List Captchas

List all captchas in folder:

```php
$result = $client->listCaptchas(
    folderId: 'b1g0ijbfaqsn12345678',
    pageSize: 50
);

foreach ($result['captchas'] as $captcha) {
    echo "- {$captcha->name} ({$captcha->id})\n";
}

// Pagination
if ($result['nextPageToken']) {
    $nextPage = $client->listCaptchas(
        folderId: 'b1g0ijbfaqsn12345678',
        pageSize: 50,
        pageToken: $result['nextPageToken']
    );
}
```

### Update Captcha

Update captcha settings:

```php
$captcha = $client->updateCaptcha(
    captchaId: 'captcha-id',
    updates: [
        'name' => 'new-name',
        'complexity' => 'HARD',
        'allowedSites' => ['example.com', 'test.com'],
    ]
);
```

### Delete Captcha

Delete a captcha:

```php
$operation = $client->deleteCaptcha('captcha-id');
echo "Operation ID: {$operation['id']}";
```

### Get Secret Key

Retrieve server secret key:

```php
$secretKey = $client->getSecretKey('captcha-id');
echo "Secret Key: {$secretKey->serverKey}";
```

## Frontend Integration

### Basic Widget

Add to your HTML:

```html
<form method="POST">
    <div id="captcha-container"></div>
    <button type="submit">Submit</button>
</form>

<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
<script>
    window.smartCaptcha = {
        sitekey: 'your-client-key',
        callback: function(token) {
            console.log('Captcha passed!');
        }
    };
</script>
```

### Advanced Widget

```html
<div id="captcha-container"></div>

<script src="https://smartcaptcha.yandexcloud.net/captcha.js?render=onload&onload=onloadFunction" defer></script>
<script>
    function onloadFunction() {
        if (window.smartCaptcha) {
            const container = document.getElementById('captcha-container');
            
            const widgetId = window.smartCaptcha.render(container, {
                sitekey: 'your-client-key',
                hl: 'en',
                callback: function(token) {
                    console.log('Token:', token);
                }
            });
        }
    }
</script>
```

## Laravel Validation Rule

Create custom validation rule:

```php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Tigusigalpa\YandexSmartCaptcha\Laravel\Facades\SmartCaptcha;

class SmartCaptchaRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $result = SmartCaptcha::validate(
            $value,
            config('smartcaptcha.secret_key'),
            request()->ip()
        );
        
        return $result->isValid();
    }
    
    public function message(): string
    {
        return 'Please complete the captcha verification.';
    }
}
```

Use in controller:

```php
$request->validate([
    'smart-token' => ['required', new SmartCaptchaRule()],
    'email' => 'required|email',
]);
```

## Error Handling

All exceptions extend `SmartCaptchaException`:

```php
use Tigusigalpa\YandexSmartCaptcha\Exceptions\{
    SmartCaptchaException,
    AuthenticationException,
    ValidationException,
    NotFoundException,
    RateLimitException
};

try {
    $result = $client->validate($token, $secret);
} catch (AuthenticationException $e) {
    // Invalid IAM token or secret key
} catch (ValidationException $e) {
    // Invalid request parameters
} catch (NotFoundException $e) {
    // Captcha not found
} catch (RateLimitException $e) {
    // Too many requests
} catch (SmartCaptchaException $e) {
    // Other errors
}
```

## Logging

Enable logging in Laravel:

```php
// config/smartcaptcha.php
'logging' => [
    'enabled' => true,
    'channel' => 'stack',
],
```

Or pass PSR-3 logger in pure PHP:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('smartcaptcha');
$logger->pushHandler(new StreamHandler('path/to/smartcaptcha.log'));

$client = new SmartCaptchaClient($iamToken, null, $logger);
```

## Testing

Run tests:

```bash
composer test
```

With coverage:

```bash
composer test-coverage
```

## Documentation

- [Yandex SmartCaptcha Documentation](https://yandex.cloud/en/docs/smartcaptcha/)
- [API Reference](https://yandex.cloud/en/docs/smartcaptcha/api-ref/)
- [Quick Start Guide](https://yandex.cloud/en/docs/smartcaptcha/quickstart)

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

**Igor Sazonov**
- GitHub: [@tigusigalpa](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository:
```bash
git clone https://github.com/tigusigalpa/yandex-smartcaptcha-php.git
cd yandex-smartcaptcha-php
```

2. Install dependencies:
```bash
composer install
```

3. Run tests:
```bash
composer test
```

### Coding Standards

This project follows PSR-12 coding standards:

```bash
composer cs-check  # Check coding standards
composer cs-fix    # Fix coding standards
composer phpstan   # Static analysis
```

### Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Changelog

### [1.0.0] - 2024-11-29

**Added:**
- Initial release
- SmartCaptcha client with full API support
- Token validation
- Captcha management (create, read, update, delete, list)
- Secret key retrieval
- Laravel integration (service provider, facade, config)
- PSR-3 logging support
- Comprehensive documentation (EN/RU)
- DTO classes for type safety
- Exception handling
- PHP 8.0+ support with strict types

## Support

- [GitHub Issues](https://github.com/tigusigalpa/yandex-smartcaptcha-php/issues)
- [Yandex Cloud Support](https://yandex.cloud/en/support)
