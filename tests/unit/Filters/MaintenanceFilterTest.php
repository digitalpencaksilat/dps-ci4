<?php

namespace Tests\Unit\Filters;

use App\Filters\MaintenanceFilter;
use CodeIgniter\Test\CIUnitTestCase;

class MaintenanceFilterTest extends CIUnitTestCase
{
    public function testPassesWhenNotInMaintenance(): void
    {
        $filter = new MaintenanceFilter();
        $this->assertNull($filter->before(service('request')));
    }
}
