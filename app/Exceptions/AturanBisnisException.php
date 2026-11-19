<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar service ketika aturan bisnis dilanggar (di luar radius, kuota habis, dst.).
 * Pesannya aman ditampilkan langsung ke pengguna. `kode` membantu UI memilih perilaku.
 */
class AturanBisnisException extends RuntimeException
{
    public function __construct(string $message, public readonly string $kode = 'umum')
    {
        parent::__construct($message);
    }
}
