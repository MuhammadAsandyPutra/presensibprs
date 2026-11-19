<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(): View
    {
        return view('admin.log.index', ['daftar' => LogAktivitas::query()->latest('id')->paginate(25)]);
    }
}
