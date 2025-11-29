<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\Laravel;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient;

/**
 * Laravel service provider for SmartCaptcha
 */
class SmartCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/smartcaptcha.php',
            'smartcaptcha'
        );

        $this->app->singleton(SmartCaptchaClient::class, function ($app) {
            $config = $app['config']['smartcaptcha'];

            $httpClient = new Client([
                'timeout' => $config['http']['timeout'] ?? 30,
            ]);

            $logger = null;
            if ($config['logging']['enabled'] ?? false) {
                $logger = $app->make(LoggerInterface::class);
                if ($config['logging']['channel'] ?? null) {
                    $logger = $app['log']->channel($config['logging']['channel']);
                }
            }

            return new SmartCaptchaClient(
                $config['oauth_token'] ?? null,
                $httpClient,
                $logger
            );
        });

        $this->app->alias(SmartCaptchaClient::class, 'smartcaptcha');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/smartcaptcha.php' => config_path('smartcaptcha.php'),
            ], 'smartcaptcha-config');
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [SmartCaptchaClient::class, 'smartcaptcha'];
    }
}
