<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Oshi-Wiki - Coming Soon</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg: #FFFFFF;
            --main: #FED7E2;
            --sub: #A0AEC0;
            --text: #2D3748;
            --soft: #F7FAFC;
            --line: #E2E8F0;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 0;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            min-width: 0;
            min-height: 100vh;
            min-height: 100svh;
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(254, 215, 226, 0.55), transparent 32%),
                radial-gradient(circle at bottom right, rgba(160, 174, 192, 0.18), transparent 34%),
                var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Hiragino Sans", "Yu Gothic", "YuGothic", "Meiryo", sans-serif;
        }

        .page {
            display: flex;
            min-height: 100vh;
            min-height: 100svh;
            width: 100%;
            align-items: center;
            justify-content: center;
            padding:
                max(24px, env(safe-area-inset-top))
                max(20px, env(safe-area-inset-right))
                max(24px, env(safe-area-inset-bottom))
                max(20px, env(safe-area-inset-left));
        }

        .wrap {
            width: 100%;
            max-width: 920px;
            min-width: 0;
            text-align: center;
        }

        .card {
            width: 100%;
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 36px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 70px rgba(45, 55, 72, 0.10);
            padding: clamp(36px, 7vw, 80px) clamp(24px, 6vw, 72px);
        }

        .logo {
            width: 100%;
            margin-bottom: clamp(24px, 5vw, 34px);
        }

        .logo img {
            display: block;
            width: 100%;
            max-width: 360px;
            height: auto;
            margin: 0 auto;
            object-fit: contain;
        }

        .badge {
            display: inline-flex;
            max-width: 100%;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--main);
            border-radius: 999px;
            background: #FFF5F7;
            color: var(--text);
            font-size: clamp(14px, 3.8vw, 16px);
            font-weight: 800;
            line-height: 1.5;
            padding: 10px 18px;
            margin-bottom: 24px;
            overflow-wrap: anywhere;
        }

        h1 {
            margin: 0;
            max-width: 100%;
            color: var(--text);
            font-size: clamp(38px, 9vw, 84px);
            line-height: 1.05;
            letter-spacing: 0.01em;
            overflow-wrap: anywhere;
        }

        .lead {
            margin: clamp(22px, 5vw, 28px) auto 0;
            max-width: 680px;
            color: #718096;
            font-size: clamp(15px, 3.8vw, 22px);
            line-height: 1.9;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .note {
            margin-top: clamp(26px, 5vw, 34px);
            padding: clamp(16px, 4vw, 20px) clamp(16px, 5vw, 24px);
            border-radius: 24px;
            background: var(--soft);
            color: #718096;
            font-size: clamp(13px, 3.5vw, 14px);
            line-height: 1.8;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .footer {
            margin-top: 24px;
            color: var(--sub);
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .page {
                align-items: flex-start;
                padding:
                    max(16px, env(safe-area-inset-top))
                    max(12px, env(safe-area-inset-right))
                    max(20px, env(safe-area-inset-bottom))
                    max(12px, env(safe-area-inset-left));
            }

            .card {
                border-radius: 26px;
                padding: 32px 18px;
            }

            .logo img {
                max-width: 280px;
            }

            .lead br {
                display: none;
            }

            .note {
                border-radius: 20px;
            }
        }

        @media (max-width: 390px) {
            .page {
                padding-right: max(10px, env(safe-area-inset-right));
                padding-left: max(10px, env(safe-area-inset-left));
            }

            .card {
                border-radius: 22px;
                padding: 28px 14px;
            }

            .logo img {
                max-width: 240px;
            }

            .badge {
                padding: 9px 14px;
            }

            h1 {
                font-size: clamp(34px, 11vw, 44px);
            }

            .lead {
                line-height: 1.8;
            }

            .note {
                padding: 15px 14px;
            }
        }

        @media (max-height: 720px) and (min-width: 641px) {
            .page {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    <div class="page">
        <main class="wrap">
            <section class="card">
                <div class="logo">
                    <img src="{{ asset('images/oshiwiki-logo.png') }}" alt="Oshi-Wiki">
                </div>

                <div class="badge">ただいま準備中です</div>

                <h1>Coming Soon</h1>

                <p class="lead">
                    Oshi-Wikiは現在、公開準備中です。<br>
                    より使いやすい創作支援データベースとして公開できるよう、調整を進めています。
                </p>

                <div class="note">
                    管理者・スタッフ向けページは通常通り利用できます。
                </div>
            </section>

            <div class="footer">© Oshi-Wiki</div>
        </main>
    </div>

    <div id="page-bottom"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'bottom']
    )
</body>
</html>
