<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonetizationService;
use App\Models\Work;
use App\Repositories\LinkClickRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkClickAnalyticsController extends Controller
{
    public function __invoke(
        Request $request,
        LinkClickRepository $repository
    ): View {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            'クリック集計は最高管理者のみ利用できます。'
        );

        $workId = $request->integer('work_id') ?: null;
        $serviceId = $request->integer('service_id') ?: null;
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        return view('admin.monetization.analytics.index', [
            'clicks' => $repository->paginate(
                30,
                $workId,
                $serviceId,
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null
            ),
            'totals' => $repository->totals(
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null
            ),
            'topLinks' => $repository->topLinks(
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null
            ),
            'works' => Work::query()
                ->whereHas('monetizationLinks')
                ->orderBy('title')
                ->get(),
            'services' => MonetizationService::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get(),
            'selectedWorkId' => $workId,
            'selectedServiceId' => $serviceId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
