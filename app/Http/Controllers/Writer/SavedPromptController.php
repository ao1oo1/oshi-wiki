<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\SavedPrompt\PreviewSavedPromptRequest;
use App\Http\Requests\Writer\SavedPrompt\StoreSavedPromptRequest;
use App\Http\Requests\Writer\SavedPrompt\UpdateSavedPromptRequest;
use App\Models\OriginalCharacter;
use App\Models\SavedPrompt;
use App\Models\Work;
use App\Models\WriterStoryAnalysis;
use App\Services\SavedPromptAiResultService;
use App\Services\SavedPromptService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Throwable;

class SavedPromptController extends Controller
{
    public function __construct(
        private readonly SavedPromptService $service,
        private readonly SavedPromptAiResultService $aiResultService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = [
            'keyword' => $request->string('keyword')->trim()->toString(),
            'writing_style' => $request->string('writing_style')->trim()->toString(),
            'genre' => $request->string('genre')->trim()->toString(),
            'status' => $request->string('status')->trim()->toString(),
            'sort' => $request->string('sort')->trim()->toString(),
        ];

        $filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        return view('writer.saved_prompts.index', array_merge(
            $this->formData($request),
            [
                'savedPrompts' => $this->service->paginateForUser($user, $filters),
                'count' => $this->service->countForUser($user),
                'limit' => WritingAssistLimits::promptsPerUser($user),
                'filters' => $filters,
            ]
        ));
    }

    public function create(Request $request): View
    {

        return view('writer.saved_prompts.create', $this->formData($request));
    }

    public function store(StoreSavedPromptRequest $request): RedirectResponse
    {
        $savedPrompt = $this->service->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('writer.prompts.show', $savedPrompt)
            ->with('success', 'プロンプトを作成しました。');
    }

    public function preview(PreviewSavedPromptRequest $request): JsonResponse
    {
        try {
            $promptBody = $this->service->previewForUser(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'prompt_body' => $promptBody,
                'length' => mb_strlen($promptBody),
            ]);
        } catch (Throwable $exception) {
            Log::error('Prompt preview failed', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'プレビュー生成中にエラーが発生しました。',
                'detail' => $exception->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, SavedPrompt $prompt): View
    {
        $this->authorizeOwner($request, $prompt);

        return view('writer.saved_prompts.show', [
            'savedPrompt' => $prompt,
            'aiResults' =>
                $this->aiResultService->latestForPrompt(
                    $request->user(),
                    $prompt
                ),
        ]);
    }

    public function edit(Request $request, SavedPrompt $prompt): View
    {

        $this->authorizeOwner($request, $prompt);

        return view('writer.saved_prompts.edit', array_merge(
            $this->formData($request),
            ['savedPrompt' => $prompt]
        ));
    }

    public function update(UpdateSavedPromptRequest $request, SavedPrompt $prompt): RedirectResponse
    {
        $this->authorizeOwner($request, $prompt);

        $this->service->update($request->user(), $prompt, $request->validated());

        return redirect()
            ->route('writer.prompts.show', $prompt)
            ->with('success', 'プロンプトを更新しました。');
    }


    public function duplicate(Request $request, SavedPrompt $prompt): RedirectResponse
    {
        $this->authorizeOwner($request, $prompt);

        $copy = $prompt->replicate();
        $copy->user_id = $request->user()->id;
        $copy->title = $prompt->title . ' のコピー';
        $copy->status = 'draft';
        $copy->used_count = 0;
        $copy->last_used_at = null;
        $copy->save();

        return redirect()
            ->route('writer.prompts.edit', $copy)
            ->with('success', 'プロンプトを複製しました。必要に応じて内容を編集してください。');
    }

    public function recordUsage(Request $request, SavedPrompt $prompt): JsonResponse
    {
        $this->authorizeOwner($request, $prompt);

        $this->service->recordUsage($prompt);

        return response()->json([
            'message' => '利用履歴を更新しました。',
            'used_count' => $prompt->used_count,
            'last_used_at' => $prompt->lastUsedLabel(),
        ]);
    }

    public function destroy(Request $request, SavedPrompt $prompt): RedirectResponse
    {
        $this->authorizeOwner($request, $prompt);

        $this->service->delete($prompt);

        return redirect()
            ->route('writer.prompts.index')
            ->with('success', 'プロンプトを削除しました。');
    }

    private function formData(Request $request): array
    {
        $user = $request->user();


        $originalCharacters = OriginalCharacter::query()
            ->forUser($user)
            ->orderBy('name')
            ->get();

        $publishedWorks = Work::query()
            ->with([
                'linkedCharacters' => function ($query): void {
                    $query
                        ->where('characters.status', 'published')
                        ->orderBy('characters.name');
                },
            ])
            ->where('status', 'published')
            ->orderBy('title')
            ->get()
            ->each(function (Work $work): void {
                $work->setRelation(
                    'characters',
                    $work->linkedCharacters
                );
            });

        $storyAnalyses = WriterStoryAnalysis::query()
            ->forUser($user)
            ->latest()
            ->limit(10)
            ->get();

        return [
            'originalCharacters' => $originalCharacters,
            'publishedWorks' => $publishedWorks,
            'storyAnalyses' => $storyAnalyses,
            'categoryLabels' => SavedPrompt::categoryLabels(),
            'writingStyleLabels' => SavedPrompt::writingStyleLabels(),
            'genreLabels' => SavedPrompt::genreLabels(),
            'limit' => WritingAssistLimits::promptsPerUser($user),
        ];
    }

    private function authorizeOwner(Request $request, SavedPrompt $savedPrompt): void
    {
        $user = $request->user();

        abort_unless($savedPrompt->user_id === $user?->id, 403);
    }
}
