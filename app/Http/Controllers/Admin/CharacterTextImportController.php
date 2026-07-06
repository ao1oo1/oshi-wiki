<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\ImportCharacterTextRequest;
use App\Models\Work;
use App\Services\CharacterService;
use App\Services\CharacterTextParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CharacterTextImportController extends Controller
{
    public function create(): View
    {
        // SUPER_ADMIN_ONLY_create
        $this->abortUnlessSuperAdmin();
        return view('admin.characters.import', [
            'works' => Work::query()->latest()->get(),
            'sampleText' => $this->sampleText(),
        ]);
    }

    public function store(
        ImportCharacterTextRequest $request,
        CharacterTextParserService $parser,
        CharacterService $characterService
    ): RedirectResponse {
        $parsed = $parser->parse($request->string('raw_text')->toString());

        $data = array_merge($parsed, [
            'work_id' => $request->integer('work_id'),
            'status' => $request->input('status', 'draft'),
        ]);

        $validator = Validator::make($data, [
            'work_id' => ['required', 'exists:works,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'grade_class' => ['nullable', 'string', 'max:255'],
            'first_person' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'string'],
            'tone_examples' => ['nullable', 'string'],
            'personality' => ['nullable', 'string'],
            'appearance' => ['nullable', 'string'],
            'background' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,private'],
        ], [], [
            'name' => '名前',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('parsed', $parsed);
        }

        $character = $characterService->create($validator->validated());

        return redirect()
            ->route('admin.characters.show', $character)
            ->with('success', 'テキストからキャラクターを登録しました。');
    }

    private function sampleText(): string
    {
        return <<<TEXT
■ミョウジ　ナマエ
名前: 苗字　名前　
読み仮名: ミョウジ　ナマエ
年齢: 20歳
所属: 〇〇学園
一人称: わたし
口調:
〜ですね。
〜だと思います。

口調の例:
「...はい。わかりました。」
「あなた、それでも学者？」

性格・特徴:
真面目な優等生。眼鏡を外すと美人。

外見の特徴:
長い黒髪。表情は薄い。

背景・経歴:
幼い頃、〇〇家の養子になる。
勉強して一流企業に入るのが夢。
TEXT;
    }

    private function abortUnlessSuperAdmin(): void
    {
        if (! auth()->user()?->is_super_admin) {
            abort(403, 'この操作は最高管理者のみ実行できます。');
        }
    }
}
