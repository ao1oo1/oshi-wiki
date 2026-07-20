<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Monetization\StoreMonetizationServiceRequest;
use App\Http\Requests\Admin\Monetization\UpdateMonetizationServiceRequest;
use App\Models\MonetizationService;
use App\Services\MonetizationServiceManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MonetizationServiceController extends Controller
{
    public function __construct(
        private readonly MonetizationServiceManagementService $service
    ) {
    }

    public function index(): View
    {
        $this->ensureSuperAdmin();

        $keyword = trim((string) request('keyword', ''));
        $category = trim((string) request('category', ''));
        $activeStatus = trim((string) request('active_status', ''));

        return view('admin.monetization.services.index', [
            'services' => $this->service->paginate(
                20,
                $keyword !== '' ? $keyword : null,
                $category !== '' ? $category : null,
                $activeStatus !== '' ? $activeStatus : null
            ),
            'keyword' => $keyword,
            'selectedCategory' => $category,
            'selectedActiveStatus' => $activeStatus,
            'categories' => MonetizationServiceManagementService::CATEGORIES,
            'totalCount' => MonetizationService::query()->count(),
        ]);
    }

    public function store(
        StoreMonetizationServiceRequest $request
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.monetization.services.index')
            ->with('success', '配信・販売サービスを登録しました。');
    }

    public function edit(MonetizationService $service): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.services.edit', [
            'service' => $service,
            'categories' => MonetizationServiceManagementService::CATEGORIES,
        ]);
    }

    public function update(
        UpdateMonetizationServiceRequest $request,
        MonetizationService $service
    ): RedirectResponse {
        $this->ensureSuperAdmin();
        $this->service->update($service, $request->validated());

        return redirect()
            ->route('admin.monetization.services.index')
            ->with('success', '配信・販売サービスを更新しました。');
    }

    public function destroy(MonetizationService $service): RedirectResponse
    {
        $this->ensureSuperAdmin();

        try {
            $this->service->delete($service);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.monetization.services.index')
            ->with('success', '配信・販売サービスを削除しました。');
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
