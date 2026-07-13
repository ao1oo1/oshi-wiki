<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\StoryAnalysis\GenerateWriterStoryAnalysisPromptRequest;
use App\Http\Requests\Writer\StoryAnalysis\StoreWriterStoryAnalysisRequest;
use App\Http\Requests\Writer\StoryAnalysis\UpdateWriterStoryAnalysisResultRequest;
use App\Models\WriterStoryAnalysis;
use App\Services\WriterStoryAnalysisService;
use App\Services\WriterStoryService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WriterStoryAnalysisController extends Controller
{
    public function __construct(
        private readonly WriterStoryAnalysisService $service,
        private readonly WriterStoryService $storyService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $count = $this->service->countForUser($user);
        $limit = WritingAssistLimits::storyAnalysesPerUser($user);

        return view('writer.story_analyses.index', [
            'analyses' => $this->service->paginateForUser($user),
            'count' => $count,
            'limit' => $limit,
            'canCreate' => $limit === null || $count < $limit,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $count = $this->service->countForUser($user);
        $limit = WritingAssistLimits::storyAnalysesPerUser($user);

        abort_if(
            $limit !== null && $count >= $limit,
            403,
            '文体分析の保存上限に達しています。'
        );

        return $this->formView($request);
    }

    public function generatePrompt(
        GenerateWriterStoryAnalysisPromptRequest $request
    ): View {
        $validated = $request->validated();

        $storyIds = collect($validated['story_ids'])
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        $prompt = $this->service->buildPrompt(
            $request->user(),
            $storyIds,
            $validated['analysis_notes'] ?? null
        );

        return $this->formView(
            $request,
            null,
            [
                'title' => $validated['title'] ?? '',
                'story_ids' => $storyIds,
                'analysis_notes' =>
                    $validated['analysis_notes'] ?? '',
                'analysis_prompt' => $prompt,
                'analysis_result' => '',
            ]
        );
    }

    public function store(
        StoreWriterStoryAnalysisRequest $request
    ): RedirectResponse {
        $analysis = $this->service->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('writer.story-analyses.show', $analysis)
            ->with('success', '文体分析を保存しました。');
    }

    public function show(
        Request $request,
        WriterStoryAnalysis $story_analysis
    ): View {
        $this->authorizeOwner($request, $story_analysis);

        return view('writer.story_analyses.show', [
            'analysis' => $this->service->prepareForDisplay(
                $request->user(),
                $story_analysis
            ),
            'analysisResultMax' =>
                WritingAssistLimits::analysisResultMaxLength(
                    $request->user()
                ),
        ]);
    }

    public function edit(
        Request $request,
        WriterStoryAnalysis $story_analysis
    ): View {
        $this->authorizeOwner($request, $story_analysis);

        return $this->formView(
            $request,
            $this->service->prepareForDisplay(
                $request->user(),
                $story_analysis
            )
        );
    }

    public function update(
        StoreWriterStoryAnalysisRequest $request,
        WriterStoryAnalysis $story_analysis
    ): RedirectResponse {
        $this->authorizeOwner($request, $story_analysis);

        $analysis = $this->service->update(
            $request->user(),
            $story_analysis,
            $request->validated()
        );

        return redirect()
            ->route('writer.story-analyses.show', $analysis)
            ->with('success', '文体分析を更新しました。');
    }

    public function updateResult(
        UpdateWriterStoryAnalysisResultRequest $request,
        WriterStoryAnalysis $story_analysis
    ): RedirectResponse {
        $this->authorizeOwner($request, $story_analysis);

        $this->service->updateResult(
            $story_analysis,
            $request->validated('analysis_result')
        );

        return redirect()
            ->route('writer.story-analyses.show', $story_analysis)
            ->with('success', '分析結果を保存しました。');
    }

    public function destroyResult(
        Request $request,
        WriterStoryAnalysis $story_analysis
    ): RedirectResponse {
        $this->authorizeOwner($request, $story_analysis);

        $this->service->deleteResult($story_analysis);

        return redirect()
            ->route('writer.story-analyses.show', $story_analysis)
            ->with('success', '分析結果を削除しました。');
    }

    public function destroy(
        Request $request,
        WriterStoryAnalysis $story_analysis
    ): RedirectResponse {
        $this->authorizeOwner($request, $story_analysis);

        $this->service->delete($story_analysis);

        return redirect()
            ->route('writer.story-analyses.index')
            ->with('success', '文体分析を削除しました。');
    }

    private function formView(
        Request $request,
        ?WriterStoryAnalysis $analysis = null,
        array $generated = []
    ): View {
        return view(
            $analysis
                ? 'writer.story_analyses.edit'
                : 'writer.story_analyses.create',
            [
                'analysis' => $analysis,
                'stories' => $this->storyService->allForUser(
                    $request->user()
                ),
                'generated' => $generated,
                'analysisResultMax' =>
                    WritingAssistLimits::analysisResultMaxLength(
                        $request->user()
                    ),
            ]
        );
    }

    private function authorizeOwner(
        Request $request,
        WriterStoryAnalysis $analysis
    ): void {
        abort_unless(
            (int) $analysis->user_id ===
                (int) $request->user()?->id,
            403
        );
    }
}
