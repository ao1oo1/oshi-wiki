<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Work;
use App\Services\WorkStorySectionService;
use App\Services\WorkStorySectionTextParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WorkStorySectionTextImportController extends Controller
{
    public function create(Work $work): View
    {
        $this->authorizeManage();

        return view(
            'admin.work_story_sections.text_import',
            [
                'work' => $work,
                'sampleText' => $this->sampleText(),
            ]
        );
    }

    public function store(
        Work $work,
        Request $request,
        WorkStorySectionTextParserService $parser,
        WorkStorySectionService $service
    ): RedirectResponse {
        $this->authorizeManage();

        $validatedInput = $request->validate([
            'raw_text' => [
                'required',
                'string',
                'max:100000',
            ],
            'status' => [
                'required',
                'in:draft,published,private',
            ],
        ]);

        $parsed = $parser->parse(
            $validatedInput['raw_text']
        );

        $parsed['status'] = $validatedInput['status'];
        $parsed['section_type'] =
            $parsed['section_type'] ?? 'chapter';
        $parsed['spoiler_level'] =
            $parsed['spoiler_level'] ?? 'none';

        $parsed['section_characters'] = collect(
            $parsed['section_characters'] ?? []
        )
            ->map(function (
                array $row
            ) use ($work): array {
                $name = trim(
                    (string) (
                        $row['character_name'] ?? ''
                    )
                );

                $matches = Character::query()
                    ->where('name', $name)
                    ->whereHas(
                        'linkedWorks',
                        fn ($query) =>
                            $query->where(
                                'works.id',
                                $work->id
                            )
                    )
                    ->get();

                if ($matches->count() !== 1) {
                    throw new \RuntimeException(
                        "キャラクター「{$name}」を"
                        . '一意に特定できません。'
                    );
                }

                unset($row['character_name']);
                $row['character_id'] =
                    $matches->first()->id;
                $row['selected'] = 1;
                $row['first_appearance'] =
                    in_array(
                        mb_strtolower(
                            trim(
                                (string) (
                                    $row['first_appearance']
                                        ?? ''
                                )
                            )
                        ),
                        ['1', 'true', 'yes', 'はい'],
                        true
                    );

                return $row;
            })
            ->values()
            ->all();

        $validator = Validator::make(
            $parsed,
            [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'section_type' => [
                    'required',
                    'in:arc,part,chapter,episode,act,prologue,epilogue,other',
                ],
                'section_number' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:9999',
                ],
                'title_kana' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'short_label' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'synopsis' => [
                    'nullable',
                    'string',
                ],
                'cumulative_settings' => [
                    'nullable',
                    'string',
                ],
                'notes' => [
                    'nullable',
                    'string',
                ],
                'spoiler_level' => [
                    'required',
                    'in:none,minor,major',
                ],
                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:9999',
                ],
                'status' => [
                    'required',
                    'in:draft,published,private',
                ],
                'events' => [
                    'nullable',
                    'array',
                    'max:100',
                ],
                'section_characters' => [
                    'nullable',
                    'array',
                ],
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $section = $service->create(
            $work,
            $parsed
        );

        return redirect()
            ->route(
                'admin.works.story-sections.show',
                [$work, $section]
            )
            ->with(
                'success',
                'テキストから章・編を登録しました。'
            );
    }

    private function sampleText(): string
    {
        return <<<'TEXT'
■ 第1章 物語の始まり
種別：chapter
章番号：1
短い表示名：1章
概要：物語が始まる章です。
この章までに登場する設定：学園と寮のルールが判明します。
ネタバレ区分：minor
表示順：1
備考：章全体の補足

物語詳細：
1. 主人公が学園へ到着する
タイミング：章冒頭
場所：学園正門
詳細：主人公が初めて学園へ足を踏み入れます。
結果：物語の舞台が提示されます。

2. 寮へ案内される
場所：学生寮
詳細：寮のルールについて説明を受けます。

登場キャラクター：
・キャラクターA
年齢：16歳
学年：1年
クラス：A組
所属：学園
役職：生徒
状態：入学直後
初登場：はい
備考：主人公

・キャラクターB
年齢：17歳
学年：2年
所属：学園
備考：案内役
TEXT;
    }

    private function authorizeManage(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '章・編のテキスト取り込みは'
            . '最高管理者のみ可能です。'
        );
    }
}
