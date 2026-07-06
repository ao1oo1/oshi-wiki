<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oshi-Wikiとは？ | Oshi-Wiki</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="oshi-header">
        <div class="oshi-container oshi-header-inner">
            <a href="{{ route('public.home') }}" class="oshi-brand">
                <img
                    src="{{ asset('images/oshi-wiki-logo.svg') }}"
                    alt="Oshi-Wiki"
                    class="oshi-public-logo-img"
                >
            </a>

            <nav class="oshi-nav">
                <a href="{{ route('public.home') }}">トップ</a>
                <a href="{{ route('public.about.show') }}" class="active">Oshi-Wikiとは？</a>
                <a href="{{ route('public.works.index') }}">作品一覧</a>
                <a href="{{ route('public.tags.index') }}">タグ一覧</a>
                <a href="{{ route('public.contact.create') }}">お問い合わせ</a>
                <a href="{{ route('login') }}">管理ログイン</a>
            </nav>
        </div>
    </header>

    <main class="oshi-container">
        <section class="oshi-hero">
            <h1>
                Oshi-Wikiとは？
            </h1>

            <p class="oshi-lead">
                作品・キャラクター情報を、創作活動の参考として確認しやすく整理するための非公式情報サイトです。
            </p>
        </section>

        <section class="oshi-section">
            <div class="oshi-card">
                <div class="oshi-about-body">
                    <h2>Oshi-Wikiとは？</h2>

                    <p>
                        Oshi-Wikiは、創作活動に必要なキャラクター情報や作品情報を、より楽に・わかりやすく確認できるようにすることを目的としたサイトです。
                    </p>

                    <p>
                        掲載情報は、できる限り客観的な内容をもとに整理していますが、誤りや情報の不足が含まれる可能性があります。<br>
                        間違いを見つけた場合は、お手数ですが
                        <a href="{{ route('public.contact.create') }}" class="oshi-chip">お問い合わせフォーム</a>
                        よりご連絡いただけますと幸いです。
                    </p>

                    <p>
                        本サイトは、作品やキャラクターを大切にしながら、創作活動の補助として利用していただくためのものです。<br>
                        作品の公式・原作者・関係者とは一切関係のない非公式サイトです。
                    </p>

                    <p>
                        ご利用の際は、著作権や各作品の公式ガイドラインを守り、モラルを持ってご利用ください。
                    </p>

                    <p>
                        原作者・公式関係者・他のファンを傷つける行為、誹謗中傷、無断転載、悪意のある改変、作品やキャラクターのイメージを著しく損なう行為はご遠慮ください。
                    </p>

                    <p>
                        また、掲載情報を利用した創作活動については、各自の責任で行ってください。<br>
                        本サイトは、創作を強制・推奨するものではなく、あくまで情報整理と創作補助を目的としています。
                    </p>

                    <p>
                        作品とキャラクター、そして創作に関わるすべての方への敬意を忘れずに、楽しくご利用ください。
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
