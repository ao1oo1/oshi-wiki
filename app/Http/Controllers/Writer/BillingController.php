<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Services\BillingEntitlementService;
use App\Services\StripeApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class BillingController extends Controller
{
    public function __construct(
        private readonly StripeApiService $stripe,
        private readonly BillingEntitlementService $entitlements
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user()->load('billingProfile.plan');

        return view('writer.billing.index', [
            'user' => $user,
            'profile' => $user->billingProfile,
            'hasPlus' => $this->entitlements->hasPlusAccess($user),
            'stripeConfigured' => $this->stripe->configured(),
            'freePlan' => config('billing.plans.free'),
            'plusPlan' => config('billing.plans.plus'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user()->load('billingProfile');

        if ($this->entitlements->hasPlusAccess($user)) {
            return redirect()
                ->route('writer.billing.index')
                ->with('status', 'すでにPlusを利用中です。');
        }

        try {
            $session = $this->stripe->createCheckoutSession($user);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('writer.billing.index')
                ->withErrors(['billing' => $exception->getMessage()]);
        }

        return redirect()->away((string) $session['url']);
    }

    public function portal(Request $request): RedirectResponse
    {
        try {
            $session = $this->stripe->createPortalSession(
                $request->user()->load('billingProfile')
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('writer.billing.index')
                ->withErrors(['billing' => $exception->getMessage()]);
        }

        return redirect()->away((string) $session['url']);
    }
}
