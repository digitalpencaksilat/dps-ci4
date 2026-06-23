<?php

namespace Tests\Unit\Filters;

use App\Filters\Ci3Globals;
use CodeIgniter\Test\CIUnitTestCase;

class Ci3GlobalsTest extends CIUnitTestCase
{
    public function testBeforeSetsAgentAndSessionOnRenderer(): void
    {
        $filter = new Ci3Globals();
        $request = service('request');

        $filter->before($request);

        $renderer = service('renderer');
        $this->assertTrue(isset($renderer->session));
    }
}
