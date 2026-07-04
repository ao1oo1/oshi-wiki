<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Work\ImportWorkTextRequest;
use App\Services\WorkService;
use App\Services\WorkTextParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WorkTextImportController extends Controller
{
    public function create(): View
    {
        return view('admin.works.import', [
            'sampleText' => $this->sampleText(),
        ]);
    }

    public function store(
        ImportWorkTextRequest $request,
        WorkTextParserService $parser,
        WorkService $workService
    ): RedirectResponse {
        $parsed = $parser->parse($request->string('raw_text')->toString());
        $parsed['status'] = $request->input('status', $parsed['status'] ?? 'draft');

        $validator = Validator::make($parsed, [
            'title' => ['required', 'string', 'max:255'],
            'title_kana' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'original_media' => ['nullable', 'string', 'max:255'],
            'official_url' => ['nullable', 'url', 'max:1000'],
            'guideline_url' => ['nullable', 'url', 'max:1000'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,private'],
        ], [], ['title' => '作品名']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parsed', $parsed);
        }

        $work = $workService->create($validator->validated());

        return redirect()
            ->route('admin.works.show', $work)
            ->with('success', 'テキストから作品を登録しました。');
    }

    private function sampleText(): string
    {
        return <<<TEXT
■作品タイトル
作品名: 作品タイトル
読み仮名: サクヒンタイトル
ジャンル: ファンタジー
原作媒体: 漫画
公式URL: https://example.com
ガイドラインURL: https://example.com/guideline
説明:
作品の概要をここに入力します。
世界観、舞台、注意事項などもまとめられます。
TEXT;
    }
}
