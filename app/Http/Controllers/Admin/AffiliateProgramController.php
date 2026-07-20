<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Monetization\StoreAffiliateProgramRequest;
use App\Http\Requests\Admin\Monetization\UpdateAffiliateProgramRequest;
use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Services\AffiliateProgramManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AffiliateProgramController extends Controller
{
    public function __construct(
        private readonly AffiliateProgramManagementService $service
    ) {
    }

    public function index(): View
    {
        $this->ensureSuperAdmin();

        $keyword = trim((string) request('keyword', ''));
        $serviceId = request()->integer('service_id') ?: null;
        $activeStatus = trim((string) request('active_status', ''));

        return view('admin.monetization.programs.index', [
            'programs' => $this->service->paginate(
                20,
                $keyword !== '' ? $keyword : null,
                $serviceId,
                $activeStatus !== '' ? $activeStatus : null
            ),
            'services' => MonetizationService::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'keyword' => $keyword,
            'selectedServiceId' => $serviceId,
            'selectedActiveStatus' => $activeStatus,
            'totalCount' => AffiliateProgram::query()->count(),
        ]);
    }

    public function store(
        StoreAffiliateProgramRequest $request
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        try {
            $this->service->create($request->validated());
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.monetization.programs.index')
            ->with('success', '提携プログラムを登録しました。');
    }

    public function edit(AffiliateProgram $program): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.programs.edit', [
            'program' => $program,
            'services' => MonetizationService::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(
        UpdateAffiliateProgramRequest $request,
        AffiliateProgram $program
    ): RedirectResponse {
        $this->ensureSuperAdmin();

        try {
            $this->service->update(
                $program,
                $request->validated()
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.monetization.programs.index')
            ->with('success', '提携プログラムを更新しました。');
    }

    public function destroy(AffiliateProgram $program): RedirectResponse
    {
        $this->ensureSuperAdmin();

        try {
            $this->service->delete($program);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.monetization.programs.index')
            ->with('success', '提携プログラムを削除しました。');
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
