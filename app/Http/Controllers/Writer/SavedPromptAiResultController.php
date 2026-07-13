<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\SavedPromptAiResult\StoreSavedPromptAiResultRequest;
use App\Models\SavedPrompt;
use App\Models\SavedPromptAiResult;
use App\Services\SavedPromptAiResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedPromptAiResultController extends Controller
{
    public function __construct(
        private readonly SavedPromptAiResultService $service
    ) {
    }

    public function store(
        StoreSavedPromptAiResultRequest $request,
        SavedPrompt $prompt
    ): RedirectResponse {
        $this->service->createForUser(
            $request->user(),
            $prompt,
            $request->validated()
        );

        return redirect()
            ->route('writer.prompts.show', $prompt)
            ->withFragment('saved-prompt-ai-results')
            ->with(
                'success',
                'AIが出した結論を保存しました。'
            );
    }

    public function destroy(
        Request $request,
        SavedPrompt $prompt,
        SavedPromptAiResult $result
    ): RedirectResponse {
        $this->service->deleteForUser(
            $request->user(),
            $prompt,
            $result
        );

        return redirect()
            ->route('writer.prompts.show', $prompt)
            ->withFragment('saved-prompt-ai-results')
            ->with(
                'success',
                '保存したAI回答を削除しました。'
            );
    }
}
