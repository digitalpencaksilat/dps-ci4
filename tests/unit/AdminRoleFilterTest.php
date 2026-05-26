<?php

use App\Filters\AdminRoleFilter;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminRoleFilterTest extends CIUnitTestCase
{
    public function testSuperAdminBypassesRoleRestriction(): void
    {
        session()->set('level', 'super_admin');

        $filter = new AdminRoleFilter();
        $request = service('request');

        $result = $filter->before($request, ['sekretariat']);

        $this->assertNull($result);
    }

    public function testUnauthorizedRoleIsRedirectedToOwnDashboard(): void
    {
        session()->set('level', 'bendahara');

        $filter = new AdminRoleFilter();
        $request = service('request');

        $result = $filter->before($request, ['sekretariat']);

        $this->assertNotNull($result);
        $this->assertSame(base_url('admin/bendahara/dashboard'), $result->getHeaderLine('Location'));
    }
}
