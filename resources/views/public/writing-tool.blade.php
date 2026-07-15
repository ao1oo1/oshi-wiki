<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>小説執筆補助ツールとは？ | Oshi-Wiki</title>
    <meta name="description" content="Oshi-Wikiの小説執筆補助ツールでできることや使い方を、初めての方向けにわかりやすくご紹介します。">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('public.partials.header')

    <main>
        <section class="writing-lp-hero">
            <div class="oshi-container writing-lp-hero-inner">
                <div class="writing-lp-hero-copy">
                    <span class="writing-lp-kicker">創作をもっと整理しやすく</span>

                    <h1>
                        キャラクター設定から<br>
                        小説用プロンプトまで、ひとつに。
                    </h1>

                    <p>
                        Oshi-Wikiの小説執筆補助ツールでは、オリジナルキャラクター、
                        関係性、ストーリー、文体などを整理し、
                        AIへ渡す執筆用プロンプトを簡単に作成できます。
                    </p>

                    <div class="writing-lp-actions">
                        <a href="{{ route('register') }}" class="writing-lp-primary-button">
                            無料で新規登録する
                        </a>

                        <a href="{{ route('login') }}" class="writing-lp-secondary-button">
                            ログイン
                        </a>
                    </div>

                    <p class="writing-lp-small-note">
                        AIと直接接続する機能ではありません。
                        作成したプロンプトをコピーして、利用中のAIサービスへ貼り付けて使います。
                    </p>
                </div>

                <div class="writing-lp-hero-visual" aria-hidden="true">
                    <div class="writing-lp-visual-card">
                        <span>CHARACTER</span>
                        <strong>登場人物設定</strong>
                        <small>名前・性格・口調・背景</small>
                    </div>

                    <div class="writing-lp-visual-card">
                        <span>RELATIONSHIP</span>
                        <strong>関係性</strong>
                        <small>呼び方・感情・出来事</small>
                    </div>

                    <div class="writing-lp-visual-card writing-lp-visual-card-accent">
                        <span>PROMPT</span>
                        <strong>執筆用プロンプト</strong>
                        <small>設定をまとめてコピー</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="oshi-container writing-lp-section">
            <div class="writing-lp-section-heading">
                <span>できること</span>
                <h2>創作に必要な情報をまとめて管理</h2>
                <p>小説を書く前の設定整理から、AIへ渡す指示文の作成までをサポートします。</p>
            </div>

            <div class="writing-lp-feature-grid">
                @foreach ([
                    ['number' => '01', 'title' => 'キャラクターを登録', 'text' => '名前、年齢、性格、外見、一人称、口調、背景などをまとめて保存できます。'],
                    ['number' => '02', 'title' => '関係性を整理', 'text' => '誰が誰をどう呼ぶか、どんな感情を持っているかを方向別に登録できます。'],
                    ['number' => '03', 'title' => 'ストーリーを保存', 'text' => '自分で書いた小説や参考にしたい文章を保存し、文体整理に活用できます。'],
                    ['number' => '04', 'title' => '文体を分析', 'text' => 'AIへ貼り付けるための文体分析プロンプトを作成し、分析結果も保存できます。'],
                    ['number' => '05', 'title' => 'プロンプトを作成', 'text' => '作品、登場人物、関係性、あらすじ、文体などを選んでひとつの指示文にまとめます。'],
                    ['number' => '06', 'title' => 'AIの回答を保存', 'text' => 'AIから返ってきたプロットや本文案を、作成したプロンプトと一緒に保存できます。'],
                ] as $feature)
                    <article class="writing-lp-feature-card">
                        <span>{{ $feature['number'] }}</span>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="writing-lp-flow">
            <div class="oshi-container">
                <div class="writing-lp-section-heading">
                    <span>使い方</span>
                    <h2>基本は3ステップ</h2>
                    <p>登録した情報を選ぶだけで、執筆に必要なプロンプトを作成できます。</p>
                </div>

                <div class="writing-lp-flow-grid">
                    @foreach ([
                        ['number' => '1', 'title' => '設定を登録', 'text' => 'キャラクターや関係性、ストーリーなどを登録します。'],
                        ['number' => '2', 'title' => 'プロンプトを組み立て', 'text' => '使いたい情報と小説の条件を選びます。'],
                        ['number' => '3', 'title' => 'コピーしてAIへ', 'text' => '完成したプロンプトをコピーして、AIサービスへ貼り付けます。'],
                    ] as $step)
                        <article class="writing-lp-flow-card">
                            <div>{{ $step['number'] }}</div>
                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ $step['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="oshi-container writing-lp-section">
            <div class="writing-lp-section-heading">
                <span>こんな方におすすめ</span>
                <h2>設定整理で迷いやすい創作者へ</h2>
            </div>

            <div class="writing-lp-recommend-grid">
                @foreach ([
                    'キャラクター設定が複数のメモに分散している',
                    '呼び方や口調の違いを整理しておきたい',
                    '長い設定を毎回AIへ入力するのが大変',
                    '自分の文体や過去作品を創作に活かしたい',
                    'プロットや本文案をまとめて保存したい',
                    '二次創作・夢小説・オリジナル小説を効率よく書きたい',
                ] as $item)
                    <div class="writing-lp-recommend-item">
                        <span>✓</span>
                        <p>{{ $item }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="writing-lp-caution">
            <div class="oshi-container">
                <div class="writing-lp-caution-card">
                    <div>
                        <span>ご利用前に</span>
                        <h2>創作を補助するためのツールです</h2>
                    </div>

                    <ul>
                        <li>AIがOshi-Wiki内で直接、小説を自動生成する機能ではありません。</li>
                        <li>登録した創作データは、公開Wikiのキャラクター情報とは分けて管理されます。</li>
                        <li>作成したプロンプトは、ご自身で利用するAIサービスへ貼り付けてください。</li>
                        <li>一次創作者や公式ガイドラインへの配慮を忘れず、モラルを守ってご利用ください。</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="writing-lp-final-cta">
            <div class="oshi-container">
                <div class="writing-lp-final-cta-card">
                    <span>無料で始められます</span>
                    <h2>設定整理から、小説づくりをもっとスムーズに。</h2>
                    <p>キャラクターや関係性を登録して、あなた専用の執筆用プロンプトを作成しましょう。</p>

                    <a href="{{ route('register') }}" class="writing-lp-primary-button">
                        無料で新規登録する
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
