<?php

namespace Tests\Unit;

use App\Support\Geo;
use PHPUnit\Framework\TestCase;

class GeoTest extends TestCase
{
    public function test_jarak_titik_yang_sama_nol(): void
    {
        $this->assertSame(0, Geo::jarak(-3.388, 114.635, -3.388, 114.635));
    }

    public function test_jarak_sekitar_111_meter_per_0001_derajat_lintang(): void
    {
        $jarak = Geo::jarak(-3.388, 114.635, -3.389, 114.635);
        $this->assertGreaterThan(105, $jarak);
        $this->assertLessThan(117, $jarak);
    }

    public function test_parse_lokasi(): void
    {
        $this->assertSame([-3.388, 114.635], Geo::parse('-3.388, 114.635'));
        $this->assertNull(Geo::parse('abc,def'));
        $this->assertNull(Geo::parse('95,10'));
        $this->assertNull(Geo::parse(null));
        $this->assertNull(Geo::parse('1,2,3'));
    }
}
