<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return view('public.legal.privacy');
    }

    public function terms(): View
    {
        return view('public.legal.terms');
    }

    public function legal(): View
    {
        return view('public.legal.legal');
    }

    public function billingPolicy(): View
    {
        return view('public.legal.billing-policy');
    }

    public function pricing(): View
    {
        return view('public.legal.pricing', [
            'plans' => config('billing.plans'),
        ]);
    }
}
