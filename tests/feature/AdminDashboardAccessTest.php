<?php

use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminDashboardAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public static function adminRouteProvider(): array
    {
        return [
            'bendahara dashboard' => ['admin/bendahara/dashboard'],
            'sekretariat dashboard' => ['admin/sekretariat/dashboard'],
            'super dashboard' => ['admin/super/dashboard'],
            'printer dashboard' => ['admin/printer/dashboard'],
            'sekretariat kontingen' => ['admin/sekretariat/kontingen'],
            'bendahara pembayaran' => ['admin/bendahara/pembayaran'],
            'bendahara kontingen' => ['admin/bendahara/kontingen'],
        ];
    }

    /**
     * @dataProvider adminRouteProvider
     */
    public function testAdminRouteRequiresLogin(string $uri): void
    {
        $result = $this->get($uri);

        $result->assertRedirectTo(base_url('admin'));
    }
}
