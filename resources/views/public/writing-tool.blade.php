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

    <style>
        .writing-tool-page-redesign {
            background: radial-gradient(circle at 12% 0%, rgba(254, 215, 226, .72), transparent 34%), radial-gradient(circle at 92% 18%, rgba(241, 245, 249, .95), transparent 34%), #fffdfd;
        }
        .writing-tool-page-redesign .writing-lp-hero { padding: 88px 0 100px; background: transparent; }
        .writing-tool-page-redesign .writing-lp-hero-inner { gap: 72px; align-items: center; }
        .writing-tool-page-redesign .writing-lp-hero-copy h1 { max-width: 760px; font-size: clamp(48px, 6vw, 84px); line-height: 1.22; letter-spacing: -.035em; }
        .writing-tool-page-redesign .writing-lp-hero-copy > p { max-width: 720px; font-size: 18px; line-height: 2; }
        .writing-tool-page-redesign .writing-lp-actions { gap: 14px; margin-top: 34px; }
        .writing-tool-page-redesign .writing-lp-primary-button,
        .writing-tool-page-redesign .writing-lp-secondary-button { min-height: 56px; padding: 14px 28px; border-radius: 18px; }
        .writing-tool-page-redesign .writing-lp-hero-visual { gap: 18px; }
        .writing-tool-page-redesign .writing-lp-visual-card { padding: 28px 30px; border-radius: 28px; box-shadow: 0 18px 45px rgba(45,55,72,.09); }
        .writing-tool-page-redesign .writing-lp-section { padding-top: 92px; padding-bottom: 92px; }
        .writing-tool-page-redesign .writing-lp-section-heading { max-width: 760px; margin-bottom: 42px; }
        .writing-tool-page-redesign .writing-lp-section-heading h2 { font-size: clamp(34px, 4vw, 54px); line-height: 1.4; }
        .writing-tool-page-redesign .writing-lp-section-heading p { font-size: 17px; line-height: 1.9; }
        .writing-tool-page-redesign .writing-lp-feature-grid { gap: 20px; }
        .writing-tool-page-redesign .writing-lp-feature-card { min-height: 250px; padding: 28px; border-radius: 26px; box-shadow: 0 14px 36px rgba(45,55,72,.06); }
        .writing-tool-page-redesign .writing-lp-flow { padding-top: 92px; padding-bottom: 92px; }
        .writing-tool-page-redesign .writing-lp-flow-grid { gap: 22px; }
        .writing-tool-page-redesign .writing-lp-flow-card { padding: 30px; border-radius: 28px; }
        .writing-tool-page-redesign .writing-lp-recommend-grid { gap: 14px; }
        .writing-tool-page-redesign .writing-lp-recommend-item { padding: 20px 22px; border-radius: 20px; }
        .writing-tool-page-redesign .writing-lp-caution { padding-top: 92px; padding-bottom: 92px; }
        .writing-tool-page-redesign .writing-lp-caution-card { padding: 42px; border-radius: 32px; }
        .writing-lp-ai-disclosure { margin: 24px 0 0; padding: 22px 24px; border-radius: 22px; background: #fff; color: #2D3748; font-size: 15px; font-weight: 700; line-height: 1.9; }
        .writing-tool-page-redesign .writing-lp-final-cta { padding-bottom: 96px; }
        .writing-tool-page-redesign .writing-lp-final-cta-card { padding: 52px; border-radius: 34px; box-shadow: 0 24px 60px rgba(45,55,72,.18); }
        .writing-lp-social-footer { padding: 34px 0 48px; border-top: 1px solid #E2E8F0; background: #fff; }
        .writing-lp-social-inner { display: flex; justify-content: center; }
        .writing-lp-x-link { display: inline-flex; align-items: center; gap: 12px; color: #2D3748; font-size: 15px; font-weight: 700; text-decoration: none; }
        .writing-lp-x-link svg { width: 24px; height: 24px; }
        @media (max-width: 760px) {
            .writing-tool-page-redesign .writing-lp-hero { padding: 56px 0 66px; }
            .writing-tool-page-redesign .writing-lp-hero-copy h1 { font-size: clamp(40px, 13vw, 58px); }
            .writing-tool-page-redesign .writing-lp-hero-copy > p { font-size: 16px; }
            .writing-tool-page-redesign .writing-lp-actions { flex-direction: column; }
            .writing-tool-page-redesign .writing-lp-primary-button,
            .writing-tool-page-redesign .writing-lp-secondary-button { width: 100%; }
            .writing-tool-page-redesign .writing-lp-section,
            .writing-tool-page-redesign .writing-lp-flow,
            .writing-tool-page-redesign .writing-lp-caution { padding-top: 66px; padding-bottom: 66px; }
            .writing-tool-page-redesign .writing-lp-caution-card,
            .writing-tool-page-redesign .writing-lp-final-cta-card { padding: 28px 22px; border-radius: 26px; }
        }
    </style>

    <style>
        /* WRITING_TOOL_LAYOUT_FIX_V2 */
        .writing-tool-page-redesign .writing-lp-hero-visual {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 18px !important;
            align-items: stretch !important;
        }

        .writing-tool-page-redesign .writing-lp-visual-card,
        .writing-tool-page-redesign .writing-lp-visual-card:nth-child(2),
        .writing-tool-page-redesign .writing-lp-visual-card-accent {
            width: 100% !important;
            min-height: 194px !important;
            margin-left: 0 !important;
        }

        .writing-tool-page-redesign .writing-lp-caution-card {
            display: block !important;
        }

        .writing-tool-page-redesign .writing-lp-caution-card > div {
            width: 100% !important;
        }

        .writing-tool-page-redesign .writing-lp-caution-card > div + div {
            margin-top: 28px !important;
        }

        .writing-tool-page-redesign .writing-lp-caution-card ul {
            max-width: none !important;
            margin: 0 !important;
            padding-left: 1.4em !important;
        }

        .writing-tool-page-redesign .writing-lp-ai-disclosure-line {
            display: block !important;
        }

        @media (max-width: 760px) {
            .writing-tool-page-redesign .writing-lp-visual-card,
            .writing-tool-page-redesign .writing-lp-visual-card:nth-child(2),
            .writing-tool-page-redesign .writing-lp-visual-card-accent {
                min-height: 0 !important;
            }

            .writing-tool-page-redesign .writing-lp-ai-disclosure-line {
                display: inline !important;
            }

            .writing-tool-page-redesign .writing-lp-ai-disclosure-line + .writing-lp-ai-disclosure-line::before {
                content: " ";
            }
        }
    </style>

</head>
<body class="writing-tool-page-redesign">
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
                        関係性、ストーリー、文体などをひとつの場所で整理し、
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
                    ['number' => '06', 'title' => '何度でもコピー', 'text' => '保存した設定からプロンプトを作り直し、利用中のAIサービスへすぐにコピーできます。'],
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

                    <p class="writing-lp-ai-disclosure">
                        <span class="writing-lp-ai-disclosure-line">
                            夢小説をWebサイトや投稿サービスなどで公開する場合は、AIを使用していることを明記してください。
                        </span>
                        <span class="writing-lp-ai-disclosure-line">
                            また、差し支えなければ <strong>#oshiwiki</strong> を付けていただけるとうれしいです。投稿された作品を読みに伺います。
                        </span>
                    </p>
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

    <footer class="writing-lp-social-footer">
        <div class="oshi-container writing-lp-social-inner">
            <a href="https://x.com/Oshi_Wiki"
               target="_blank"
               rel="noopener noreferrer"
               class="writing-lp-x-link"
               aria-label="Oshi-Wiki公式Xアカウントを開く">
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                    <path d="M18.244 2H21.552L14.325 10.26L22.827 22H16.17L10.956 15.183L4.99 22H1.68L9.413 13.165L1.258 2H8.084L12.797 8.231L18.244 2ZM17.083 19.932H18.916L7.089 3.96H5.122L17.083 19.932Z"/>
                </svg>
                <span>@Oshi_Wiki</span>
            </a>
        </div>
    </footer>
</body>
</html>
