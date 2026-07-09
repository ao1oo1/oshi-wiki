<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function __invoke(): View
    {
        return view('writer.guide');
    }
}
