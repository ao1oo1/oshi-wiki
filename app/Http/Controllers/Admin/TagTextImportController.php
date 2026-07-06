<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\ImportTagTextRequest;
use App\Services\TagService;
use App\Services\TagTextParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TagTextImportController extends Controller
{
    public function create(): View
    {
        // SUPER_ADMIN_ONLY_create
        $this->abortUnlessSuperAdmin();
        return view('admin.tags.import', [
            'sampleText' => $this->sampleText(),
        ]);
    }

    public function store(
        ImportTagTextRequest $request,
        TagTextParserService $parser,
        TagService $tagService
    ): RedirectResponse {
        $parsed = $parser->parse($request->string('raw_text')->toString());
        $parsed['status'] = $request->input('status', $parsed['status'] ?? 'draft');

        $validator = Validator::make($parsed, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,private'],
        ], [], ['name' => 'タグ名']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parsed', $parsed);
        }

        $tag = $tagService->create($validator->validated());

        return redirect()
            ->route('admin.tags.edit', $tag)
            ->with('success', 'テキストからタグを登録しました。');
    }

    private function sampleText(): string
    {
        return <<<TEXT
■学園
タグ名: 学園
種類: genre
説明:
学校や学園を舞台にした作品・キャラクターに使うタグです。
TEXT;
    }

    private function abortUnlessSuperAdmin(): void
    {
        if (! auth()->user()?->is_super_admin) {
            abort(403, 'この操作は最高管理者のみ実行できます。');
        }
    }
}
