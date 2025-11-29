<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexSmartCaptcha\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexSmartCaptcha\DTO\ValidationResult;
use Tigusigalpa\YandexSmartCaptcha\Exceptions\AuthenticationException;
use Tigusigalpa\YandexSmartCaptcha\SmartCaptchaClient;

class SmartCaptchaClientTest extends TestCase
{
    private function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    public function testValidateSuccess(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'status' => 'ok',
            'host' => 'example.com',
        ]));

        $mockClient = $this->createMockClient([$mockResponse]);
        $client = new SmartCaptchaClient(null, $mockClient);

        $result = $client->validate('test-token', 'test-secret', '127.0.0.1');

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals('ok', $result->status);
        $this->assertEquals('example.com', $result->host);
    }

    public function testValidateFailure(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'status' => 'failed',
            'message' => 'Invalid token',
        ]));

        $mockClient = $this->createMockClient([$mockResponse]);
        $client = new SmartCaptchaClient(null, $mockClient);

        $result = $client->validate('invalid-token', 'test-secret');

        $this->assertFalse($result->isValid());
        $this->assertEquals('failed', $result->status);
        $this->assertEquals('Invalid token', $result->message);
    }

    public function testSetIamToken(): void
    {
        $client = new SmartCaptchaClient();
        $result = $client->setIamToken('new-token');

        $this->assertSame($client, $result);
    }

    public function testCreateCaptchaRequiresAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('IAM token is required');

        $client = new SmartCaptchaClient();
        $client->createCaptcha('folder-id', 'test-captcha');
    }

    public function testGetCaptchaRequiresAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('IAM token is required');

        $client = new SmartCaptchaClient();
        $client->getCaptcha('captcha-id');
    }

    public function testListCaptchasRequiresAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('IAM token is required');

        $client = new SmartCaptchaClient();
        $client->listCaptchas('folder-id');
    }
}
