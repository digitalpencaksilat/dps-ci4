<?php

namespace Tests\Unit\Services;

use App\Services\RecaptchaService;
use CodeIgniter\Test\CIUnitTestCase;

class RecaptchaServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIsConfiguredReturnsFalseWhenSecretKeyEmpty(): void
    {
        $_ENV['recaptcha.secretKey'] = '';
        $service = new RecaptchaService();
        $this->assertFalse($service->isConfigured());
    }

    public function testIsConfiguredReturnsTrueWhenSecretKeySet(): void
    {
        $_ENV['recaptcha.secretKey'] = 'test-secret-key';
        $service = new RecaptchaService();
        $this->assertTrue($service->isConfigured());
    }

    public function testSiteKeyReturnsEmptyWhenNotSet(): void
    {
        $_ENV['recaptcha.siteKey'] = '';
        $service = new RecaptchaService();
        $this->assertSame('', $service->siteKey());
    }

    public function testSiteKeyReturnsConfiguredValue(): void
    {
        $_ENV['recaptcha.siteKey'] = 'test-site-key';
        $service = new RecaptchaService();
        $this->assertSame('test-site-key', $service->siteKey());
    }

    public function testVerifyReturnsTrueWhenNotConfigured(): void
    {
        $_ENV['recaptcha.secretKey'] = '';
        $service = new RecaptchaService();
        $this->assertTrue($service->verify(null));
        $this->assertTrue($service->verify('some-token'));
    }

    public function testVerifyReturnsFalseWhenNoTokenGiven(): void
    {
        $_ENV['recaptcha.secretKey'] = 'test-secret-key';
        $service = new RecaptchaService();
        $this->assertFalse($service->verify(null));
        $this->assertFalse($service->verify(''));
    }
}
