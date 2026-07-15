<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WritingToolController extends Controller
{
    public function show(): View
    {
        return view('public.writing-tool');
    }
}
