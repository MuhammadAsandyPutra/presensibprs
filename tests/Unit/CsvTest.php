<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function test_sel_berawalan_rumus_dinetralkan(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", Csv::aman('=HYPERLINK("x")'));
        $this->assertSame("'+1", Csv::aman('+1'));
        $this->assertSame('Budi', Csv::aman('Budi'));
        $this->assertSame(5, Csv::aman(5));
    }
}
