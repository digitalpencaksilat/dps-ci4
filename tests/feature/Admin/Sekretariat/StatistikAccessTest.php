<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class StatistikAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * @return list<array{string}>
     */
    public static function routeProvider(): array
    {
        return [
            ['admin/sekretariat/statistik'],
            ['admin/sekretariat/statistik/tanding'],
            ['admin/sekretariat/statistik/seni'],
        ];
    }

    /**
     * @dataProvider routeProvider
     */
    public function testGuestIsRedirectedToAdminLogin(string $uri): void
    {
        $result = $this->get($uri);

        $result->assertRedirectTo(base_url('admin'));
    }

    /**
     * @dataProvider routeProvider
     */
    public function testUnauthorizedAdminRoleIsRedirectedToOwnDashboard(string $uri): void
    {
        $result = $this->withSession([
            'level' => 'bendahara',
            'nama'  => 'Bendahara Test',
        ])->get($uri);

        $result->assertRedirectTo(base_url('admin/bendahara/dashboard'));
    }
}
