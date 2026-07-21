<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Services\BillingEntitlementService;
use App\Services\WriterCsvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WriterCsvController extends Controller
{
    public function __construct(
        private readonly WriterCsvService $csv,
        private readonly BillingEntitlementService $entitlements
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user()->load('billingProfile.plan');

        return view('writer.csv.index', [
            'types' => WriterCsvService::TYPES,
            'hasPlus' =>
                $this->entitlements->hasPlusAccess($user),
        ]);
    }

    public function guide(): View
    {
        return view('writer.csv.guide');
    }

    public function export(
        Request $request,
        string $type
    ): StreamedResponse {
        return $this->csv->export($request->user(), $type);
    }

    public function sample(string $type): StreamedResponse
    {
        return $this->csv->sample($type);
    }

    public function import(
        Request $request,
        string $type
    ): RedirectResponse {
        $user = $request->user()->load('billingProfile.plan');

        abort_unless(
            $this->entitlements->hasPlusAccess($user),
            403,
            'CSVインポートはPlus限定機能です。'
        );

        $validated = $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
        ]);

        $created = $this->csv->import(
            $user,
            $type,
            $validated['csv_file']
        );

        return redirect()
            ->route('writer.csv.index')
            ->with(
                'success',
                sprintf(
                    '%sを%d件取り込みました。',
                    WriterCsvService::TYPES[$type],
                    $created
                )
            );
    }
}
