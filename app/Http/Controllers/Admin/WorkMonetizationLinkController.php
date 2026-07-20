<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Monetization\StoreWorkMonetizationLinkRequest;
use App\Http\Requests\Admin\Monetization\UpdateWorkMonetizationLinkRequest;
use App\Http\Requests\Admin\Monetization\UpdateWorkMonetizationSettingsRequest;
use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use App\Services\LinkVerificationService;
use App\Services\WorkMonetizationLinkManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkMonetizationLinkController extends Controller
{
    public function __construct(
        private readonly WorkMonetizationLinkManagementService $service
    ) {
    }

    public function index(Work $work): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.work-links.index', [
            'work' => $work->load('parentWork'),
            'links' => $this->service->forWork($work),
            'services' => MonetizationService::query()
                ->where('is_active', true)
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'programs' => AffiliateProgram::query()
                ->with('service')
                ->where('is_active', true)
                ->orderBy('service_id')
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'productTypes' =>
                WorkMonetizationLinkManagementService::PRODUCT_TYPES,
            'availabilityStatuses' =>
                WorkMonetizationLinkManagementService::AVAILABILITY_STATUSES,
            'inheritanceOptions' =>
                WorkMonetizationLinkManagementService::INHERITANCE_OPTIONS,
        ]);
    }

    public function store(
        StoreWorkMonetizationLinkRequest $request,
        Work $work
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        try {
            $this->service->create($work, $request->validated());
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.works.monetization-links.index', $work)
            ->with('success', '作品商品リンクを登録しました。');
    }

    public function edit(
        Work $work,
        WorkMonetizationLink $monetizationLink
    ): View {
        $this->ensureSuperAdmin();
        $this->ensureLinkBelongsToWork($work, $monetizationLink);

        return view('admin.monetization.work-links.edit', [
            'work' => $work,
            'link' => $monetizationLink,
            'services' => MonetizationService::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'programs' => AffiliateProgram::query()
                ->with('service')
                ->orderBy('service_id')
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'productTypes' =>
                WorkMonetizationLinkManagementService::PRODUCT_TYPES,
            'availabilityStatuses' =>
                WorkMonetizationLinkManagementService::AVAILABILITY_STATUSES,
        ]);
    }

    public function update(
        UpdateWorkMonetizationLinkRequest $request,
        Work $work,
        WorkMonetizationLink $monetizationLink
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        try {
            $this->service->update(
                $work,
                $monetizationLink,
                $request->validated()
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.works.monetization-links.index', $work)
            ->with('success', '作品商品リンクを更新しました。');
    }

    public function destroy(
        Work $work,
        WorkMonetizationLink $monetizationLink
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $this->service->delete($work, $monetizationLink);

        return redirect()
            ->route('admin.works.monetization-links.index', $work)
            ->with('success', '作品商品リンクを削除しました。');
    }

    public function verify(
        Work $work,
        WorkMonetizationLink $monetizationLink,
        LinkVerificationService $verificationService
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $this->ensureLinkBelongsToWork($work, $monetizationLink);

        $result = $verificationService->verify(
            $monetizationLink,
            'manual'
        );

        return redirect()
            ->route('admin.works.monetization-links.index', $work)
            ->with(
                'success',
                'リンク検証が完了しました。結果：'
                . WorkMonetizationLinkManagementService::AVAILABILITY_STATUSES[
                    $result['status']
                ]
            );
    }

    public function verifyAll(
        LinkVerificationService $verificationService
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        $summary = $verificationService->verifyActiveLinks(
            'manual_bulk'
        );

        return redirect()
            ->route('admin.monetization.analytics.index')
            ->with(
                'success',
                sprintf(
                    '全件検証が完了しました。対象%d件／利用可能%d件／確認中%d件／未確認%d件／終了%d件',
                    $summary['total'],
                    $summary['available'],
                    $summary['checking'],
                    $summary['unknown'],
                    $summary['ended']
                )
            );
    }

    public function updateSettings(
        UpdateWorkMonetizationSettingsRequest $request,
        Work $work
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        try {
            $this->service->updateWorkSettings(
                $work,
                $request->validated()
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.works.monetization-links.index', $work)
            ->with('success', '作品の収益化設定を更新しました。');
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '収益管理は最高管理者のみ利用できます。'
        );
    }

    private function ensureLinkBelongsToWork(
        Work $work,
        WorkMonetizationLink $link
    ): void {
        abort_unless(
            (int) $link->work_id === (int) $work->id,
            404
        );
    }
}
