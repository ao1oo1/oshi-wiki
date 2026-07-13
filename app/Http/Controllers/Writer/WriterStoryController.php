<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\Story\GenerateStoryAnalysisPromptRequest;
use App\Http\Requests\Writer\StoryAnalysis\StoreWriterStoryAnalysisRequest;

use App\Http\Requests\Writer\Story\StoreWriterStoryRequest;
use App\Http\Requests\Writer\Story\UpdateWriterStoryRequest;
use App\Models\WriterStory;
use App\Services\WriterStoryAnalysisService;
use App\Services\WriterStoryService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WriterStoryController extends Controller
{
    public function __construct(
        private readonly WriterStoryService $service,
        private readonly WriterStoryAnalysisService $analysisService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = array_filter([
            'keyword' => $request->string('keyword')->trim()->toString(),
            'status' => $request->string('status')->trim()->toString(),
            'sort' => $request->string('sort')->trim()->toString(),
        ], fn ($value) => $value !== '');

        return view('writer.stories.index', [
            'stories' => $this->service->paginateForUser($user, $filters),
            'count' => $this->service->countForUser($user),
            'limit' => WritingAssistLimits::storiesPerUser($user),
            'filters' => $filters,
        ]);
    }

    public function analysis(Request $request): View
    {
        return view('writer.stories.analysis', [
            'stories' => $this->service->allForUser($request->user()),
            'analysisPrompt' => null,
            'selectedStoryIds' => [],
            'analysisNotes' => '',
            'savedAnalyses' =>
                $this->analysisService->paginateForUser(
                    $request->user()
                ),
        ]);
    }

    public function generateAnalysisPrompt(
        GenerateStoryAnalysisPromptRequest $request
    ): View {
        $validated = $request->validated();

        $selectedStoryIds = array_map(
            'intval',
            $validated['story_ids']
        );

        return view('writer.stories.analysis', [
            'stories' => $this->service->allForUser($request->user()),
            'analysisPrompt' => $this->service->buildAnalysisPrompt(
                $request->user(),
                $selectedStoryIds,
                $validated['analysis_notes'] ?? null
            ),
            'selectedStoryIds' => $selectedStoryIds,
            'analysisNotes' =>
                $validated['analysis_notes'] ?? '',
            'savedAnalyses' =>
                $this->analysisService->paginateForUser(
                    $request->user()
                ),
        ]);
    }

    public function storeAnalysisResult(
        StoreWriterStoryAnalysisRequest $request
    ): RedirectResponse {
        $this->analysisService->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('writer.stories.analysis')
            ->with(
                'success',
                'AIの文体分析結果を保存しました。'
            );
    }

    public function create(Request $request): View
    {
        return view('writer.stories.create', [
            'count' => $this->service->countForUser($request->user()),
            'limit' => WritingAssistLimits::storiesPerUser($request->user()),
        ]);
    }

    public function store(
        StoreWriterStoryRequest $request
    ): RedirectResponse {
        $story = $this->service->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('writer.stories.show', $story)
            ->with('success', 'ストーリーを登録しました。');
    }

    public function show(
        Request $request,
        WriterStory $story
    ): View {
        $this->authorizeOwner($request, $story);

        return view('writer.stories.show', [
            'story' => $story,
        ]);
    }

    public function edit(
        Request $request,
        WriterStory $story
    ): View {
        $this->authorizeOwner($request, $story);

        return view('writer.stories.edit', [
            'story' => $story,
        ]);
    }

    public function update(
        UpdateWriterStoryRequest $request,
        WriterStory $story
    ): RedirectResponse {
        $this->authorizeOwner($request, $story);

        $this->service->update(
            $story,
            $request->validated()
        );

        return redirect()
            ->route('writer.stories.show', $story)
            ->with('success', 'ストーリーを更新しました。');
    }

    public function destroy(
        Request $request,
        WriterStory $story
    ): RedirectResponse {
        $this->authorizeOwner($request, $story);

        $this->service->delete($story);

        return redirect()
            ->route('writer.stories.index')
            ->with('success', 'ストーリーを削除しました。');
    }

    private function authorizeOwner(
        Request $request,
        WriterStory $story
    ): void {
        abort_unless(
            (int) $story->user_id === (int) $request->user()?->id,
            403
        );
    }
}
