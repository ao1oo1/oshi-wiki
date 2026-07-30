<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Monetization\StoreImpressionAdSlotRequest;
use App\Http\Requests\Admin\Monetization\UpdateImpressionAdSlotRequest;
use App\Models\ImpressionAdSlot;
use App\Models\MonetizationService;
use App\Services\ImpressionAdSlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImpressionAdSlotController extends Controller
{
    public function __construct(
        private readonly ImpressionAdSlotService $service
    ) {
    }

    public function index(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.ad-slots.index', [
            'slots' => ImpressionAdSlot::query()
                ->with('service')
                ->orderBy('page_scope')
                ->orderBy('position')
                ->orderBy('priority')
                ->orderBy('id')
                ->paginate(30),
            'services' => $this->impressionServices(),
            'pageScopes' => ImpressionAdSlotService::PAGE_SCOPES,
            'positions' => ImpressionAdSlotService::POSITIONS,
            'deviceTypes' => ImpressionAdSlotService::DEVICE_TYPES,
        ]);
    }

    public function store(
        StoreImpressionAdSlotRequest $request
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        $this->service->create(
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('admin.monetization.ad-slots.index')
            ->with('success', '広告スロットを登録しました。');
    }

    public function edit(ImpressionAdSlot $adSlot): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.ad-slots.edit', [
            'adSlot' => $adSlot,
            'services' => $this->impressionServices(),
            'pageScopes' => ImpressionAdSlotService::PAGE_SCOPES,
            'positions' => ImpressionAdSlotService::POSITIONS,
            'deviceTypes' => ImpressionAdSlotService::DEVICE_TYPES,
        ]);
    }

    public function update(
        UpdateImpressionAdSlotRequest $request,
        ImpressionAdSlot $adSlot
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        $this->service->update(
            $adSlot,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('admin.monetization.ad-slots.index')
            ->with('success', '広告スロットを更新しました。');
    }

    public function destroy(
        ImpressionAdSlot $adSlot
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        $this->service->delete($adSlot);

        return redirect()
            ->route('admin.monetization.ad-slots.index')
            ->with('success', '広告スロットを削除しました。');
    }

    private function impressionServices()
    {
        return MonetizationService::query()
            ->where('revenue_model', 'impression')
            ->where('is_active', true)
            ->whereNotNull('impression_script')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '収益管理は最高管理者のみ利用できます。'
        );
    }
}
