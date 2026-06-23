<?php

use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminLoginFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testAdminLoginPageIsAccessible(): void
    {
        $result = $this->get('admin');

        $result->assertOK();
    }

    public function testAdminDashboardRequiresAuth(): void
    {
        $result = $this->get('admin/bendahara/dashboard');

        $result->assertRedirectTo(base_url('admin'));
    }
}
