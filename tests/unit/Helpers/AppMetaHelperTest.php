<?php

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

class AppMetaHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('app_meta');
    }

    public function testAppVersionReturnsString(): void
    {
        $version = app_version();
        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }

    public function testFormatTanggalIndoWithValidDate(): void
    {
        $result = format_tanggal_indo('2025-01-15');
        $this->assertSame('15 Januari 2025', $result);
    }

    public function testFormatTanggalIndoWithNullReturnsDash(): void
    {
        $result = format_tanggal_indo(null);
        $this->assertSame('-', $result);
    }

    public function testFormatTanggalIndoWithEmptyStringReturnsDash(): void
    {
        $result = format_tanggal_indo('');
        $this->assertSame('-', $result);
    }

    public function testFormatTanggalIndoAllMonths(): void
    {
        $expected = [
            '2024-01-10' => '10 Januari 2024',
            '2024-02-20' => '20 Februari 2024',
            '2024-03-30' => '30 Maret 2024',
            '2024-04-05' => '5 April 2024',
            '2024-05-15' => '15 Mei 2024',
            '2024-06-25' => '25 Juni 2024',
            '2024-07-12' => '12 Juli 2024',
            '2024-08-18' => '18 Agustus 2024',
            '2024-09-22' => '22 September 2024',
            '2024-10-31' => '31 Oktober 2024',
            '2024-11-11' => '11 November 2024',
            '2024-12-01' => '1 Desember 2024',
        ];

        foreach ($expected as $date => $expectedValue) {
            $this->assertSame($expectedValue, format_tanggal_indo($date));
        }
    }
}
