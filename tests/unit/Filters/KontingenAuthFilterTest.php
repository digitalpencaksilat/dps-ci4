<?php

namespace Tests\Unit\Filters;

use App\Filters\KontingenAuthFilter;
use CodeIgniter\Test\CIUnitTestCase;

class KontingenAuthFilterTest extends CIUnitTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        $filter = new KontingenAuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertNotNull($result);
        $this->assertStringContainsString('pendaftaran/login', $result->getHeaderLine('Location'));
    }

    public function testNonKontingenLevelIsRedirected(): void
    {
        session()->set('level', 'admin');
        session()->set('id_kontingen', 1);

        $filter = new KontingenAuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertNotNull($result);
    }

    public function testValidKontingenPasses(): void
    {
        session()->set('level', 'kontingen');
        session()->set('id_kontingen', 1);

        $filter = new KontingenAuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertNull($result);
    }

    public function testKontingenWithoutIdIsRedirected(): void
    {
        session()->set('level', 'kontingen');

        $filter = new KontingenAuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertNotNull($result);
    }
}
