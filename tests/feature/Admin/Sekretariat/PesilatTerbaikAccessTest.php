<?php

use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PesilatTerbaikAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * @return list<array{string}>
     */
    public static function routeProvider(): array
    {
        return [
            ['admin/sekretariat/pesilat-terbaik/pertandingan-tanding'],
            ['admin/sekretariat/pesilat-terbaik/battle-seni'],
            ['admin/sekretariat/pesilat-terbaik/pool-seni'],
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
