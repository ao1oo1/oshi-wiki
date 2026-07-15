<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\ImportCharacterCsvRequest;
use App\Models\Work;
use App\Services\CharacterCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CharacterCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        return view('admin.characters.csv-import', [
            'works' => Work::query()->latest()->get(),
        ]);
    }

    public function store(
        ImportCharacterCsvRequest $request,
        CharacterCsvImportService $importService
    ): RedirectResponse {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->filled('work_id') ? $request->integer('work_id') : null,
            $request->input('default_status', 'draft')
        );

        $created = $result['created'] ?? $result['imported'];
        $updated = $result['updated'] ?? 0;

        $message = "CSVから{$created}件のキャラクターを新規登録し、{$updated}件を更新しました。";

        if ($result['skipped'] > 0) {
            $message .= " 空行{$result['skipped']}件をスキップしました。";
        }

        return redirect()
            ->route('admin.characters.csv-import.create')
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        $csv = $this->sampleCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-sample.csv"',
        ]);
    }

    private function sampleCsv(): string
    {
        $headers = [
            'character_id',
            'work_id',
            'primary_work_id',
            'work_ids',
            'primary_work_title',
            'work_titles',
            'name',
            'name_kana',
            'real_name',
            'aliases',
            'name_english',
            'gender',
            'age',
            'birthday',
            'height',
            'weight',
            'blood_type',
            'birthplace',
            'species',
            'affiliation',
            'school_grade_class',
            'occupation_position',
            'family_structure',
            'appearance',
            'personality',
            'first_person',
            'second_person',
            'basic_tone',
            'catchphrases',
            'distinctive_speech',
            'tone_by_relationship',
            'short_quote_examples',
            'abilities',
            'background',
            'story_activities',
            'source_title',
            'source_url',
            'source_type',
            'source_reliability',
            'source_checked_at',
            'spoiler_level',
            'status',
            'tag_ids',
            'tag_names',
        ];

        $sample = [
            '',
            '',
            '苗字　名前',
            'みょうじ　なまえ',
            '',
            '愛称、別名',
            'Firstname Lastname',
            '女性',
            '16歳',
            '9月3日',
            '160cm',
            '非公開',
            'A型',
            '東京都',
            '人間',
            '〇〇学園',
            '1年A組',
            '生徒',
            '父、母、兄',
            '長い黒髪と青い瞳。',
            '真面目で責任感が強い。',
            'わたし',
            'あなた',
            '丁寧な口調。',
            '「なるほど」が口癖。',
            '語尾に「ですね」を使う。',
            '親しい相手には砕けた話し方になる。',
            '「はい、わかりました。」',
            '剣術を得意とする。',
            '幼い頃から〇〇で育った。',
            '第1章で初登場し、事件解決に協力する。',
            '公式キャラクター紹介',
            'https://example.com/character',
            'official',
            'high',
            date('Y-m-d'),
            'none',
            'draft',
            '',
            '主人公,学生',
        ];

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers, ',', '"', '');
        fputcsv($handle, $sample, ',', '"', '');
        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
