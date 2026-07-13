<?php

namespace App\Services;

use App\Models\User;
use App\Models\WriterStory;
use App\Repositories\WriterStoryRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Validation\ValidationException;

class WriterStoryService
{
    public function __construct(
        private readonly WriterStoryRepository $repository
    ) {
    }

    public function paginateForUser(
        User $user,
        array $filters = []
    ) {
        return $this->repository->paginateForUser($user, $filters);
    }

    public function allForUser(User $user)
    {
        return $this->repository->allForUser($user);
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function createForUser(
        User $user,
        array $data
    ): WriterStory {
        $limit = WritingAssistLimits::storiesPerUser($user);

        if (
            $limit !== null
            && $this->repository->countForUser($user) >= $limit
        ) {
            throw ValidationException::withMessages([
                'limit' => "ストーリーは最大{$limit}件まで登録できます。",
            ]);
        }

        $data['user_id'] = $user->id;
        $data['status'] = $data['status'] ?? 'active';

        return $this->repository->create($data);
    }

    public function update(
        WriterStory $story,
        array $data
    ): bool {
        $data['status'] = $data['status'] ?? 'active';

        return $this->repository->update($story, $data);
    }

    public function buildAnalysisPrompt(
        User $user,
        array $storyIds,
        ?string $analysisNotes = null
    ): string {
        $storyIds = array_values(array_unique(array_map(
            'intval',
            $storyIds
        )));

        $stories = $this->repository->findSelectedForUser(
            $user,
            $storyIds
        );

        if ($stories->isEmpty()) {
            throw ValidationException::withMessages([
                'story_ids' => '分析できるストーリーが見つかりませんでした。',
            ]);
        }

        if ($stories->count() !== count($storyIds)) {
            throw ValidationException::withMessages([
                'story_ids' => '選択内容に閲覧できないストーリーが含まれています。',
            ]);
        }

        $lines = [
            '以下に、私が執筆した複数のストーリーを提示します。',
            'これらの文章を分析し、同じ作者らしい文章表現を再現するためのプロンプトを作成してください。',
            '',
            '本文をそのまま書き写したり、固有の展開を複製したりするのではなく、',
            '文章の特徴・構成・表現傾向を抽象化して分析してください。',
            '',
            '【分析してほしい項目】',
            '1. 文体の特徴',
            '・一文の長さ',
            '・改行の頻度',
            '・語彙の難易度',
            '・漢字、ひらがな、カタカナの使い方',
            '・語尾、接続詞、修飾表現の傾向',
            '',
            '2. 視点と語り方',
            '・一人称、三人称などの視点',
            '・語り手と登場人物の距離感',
            '・内面描写と客観描写の割合',
            '',
            '3. 会話表現',
            '・会話文と地の文の割合',
            '・セリフの長さ',
            '・会話のテンポ',
            '・話者の感情や動作の挟み方',
            '',
            '4. キャラクター描写',
            '・性格の見せ方',
            '・感情表現',
            '・表情、仕草、視線、間の使い方',
            '・人物同士の距離感の描き方',
            '',
            '5. 情景描写',
            '・場所、時間、天候、音、匂いなどの扱い',
            '・五感描写の傾向',
            '・背景描写の詳しさ',
            '',
            '6. 物語構成',
            '・場面の始め方と終わらせ方',
            '・起承転結の組み立て方',
            '・伏線や情報開示の方法',
            '・場面転換の方法',
            '',
            '7. テンポと文章リズム',
            '・展開速度',
            '・緊張と緩和',
            '・短文と長文の使い分け',
            '',
            '8. 作者らしさ',
            '・繰り返し現れる特徴',
            '・特徴的な表現',
            '・読後感',
            '・避けていると思われる表現',
            '',
            '【最終出力】',
            '分析結果を整理したあと、',
            '新しい小説を執筆するAIにそのまま渡せる',
            '「文体・構成再現用プロンプト」をコードブロックで出力してください。',
            '',
            '再現用プロンプトには、次の内容を含めてください。',
            '・守るべき文体',
            '・視点',
            '・会話と地の文の割合',
            '・描写の濃さ',
            '・感情表現の方法',
            '・文章リズム',
            '・場面構成',
            '・避けるべき表現',
            '・不足情報を勝手に断定しないこと',
            '',
            '抽象的な説明だけではなく、',
            'AIが実際の執筆時に守れる具体的な指示文にしてください。',
        ];

        $analysisNotes = trim((string) $analysisNotes);

        if ($analysisNotes !== '') {
            $lines[] = '';
            $lines[] = '【追加で重視してほしいこと】';
            $lines[] = $analysisNotes;
        }

        $lines[] = '';
        $lines[] = '==================================================';
        $lines[] = '【分析対象ストーリー】';
        $lines[] = '==================================================';

        foreach ($stories as $index => $story) {
            $number = $index + 1;

            $lines[] = '';
            $lines[] = "【ストーリー{$number}】";
            $lines[] = 'タイトル：' . $story->title;
            $lines[] = '話数：' . (
                $story->episode_number
                    ? '第' . $story->episode_number . '話'
                    : '未設定'
            );

            if (filled($story->memo)) {
                $lines[] = '執筆メモ：';
                $lines[] = $story->memo;
            }

            $lines[] = '';
            $lines[] = '本文：';
            $lines[] = $story->body;
            $lines[] = '';
            $lines[] = '--------------------------------------------------';
        }

        return implode(PHP_EOL, $lines);
    }

    public function delete(WriterStory $story): bool
    {
        return $this->repository->delete($story);
    }
}
