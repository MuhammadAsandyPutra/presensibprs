<?php

namespace Tests\Unit;

use App\Support\Waktu;
use PHPUnit\Framework\TestCase;

class WaktuTest extends TestCase
{
    public function test_detik(): void
    {
        $this->assertSame(30615, Waktu::detik('08:30:15'));
        $this->assertSame(32400, Waktu::detik('09:00'));
        $this->assertSame(0, Waktu::detik(null));
    }

    public function test_hm(): void
    {
        $this->assertSame('08:30', Waktu::hm('08:30:15'));
        $this->assertSame('--:--', Waktu::hm(null));
    }
}
