<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oshi-Wiki - Coming Soon</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg: #FFFFFF;
            --main: #FED7E2;
            --sub: #A0AEC0;
            --text: #2D3748;
            --soft: #F7FAFC;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(254, 215, 226, 0.55), transparent 32%),
                radial-gradient(circle at bottom right, rgba(160, 174, 192, 0.18), transparent 34%),
                var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Hiragino Sans", "Yu Gothic", "YuGothic", "Meiryo", sans-serif;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .wrap {
            width: min(920px, 100%);
            text-align: center;
        }

        .card {
            border: 1px solid #E2E8F0;
            border-radius: 36px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 24px 70px rgba(45, 55, 72, 0.10);
            padding: clamp(36px, 7vw, 80px) clamp(24px, 6vw, 72px);
        }

        .logo {
            margin-bottom: 28px;
            font-weight: 900;
            font-size: clamp(28px, 5vw, 44px);
            letter-spacing: 0.02em;
        }

        .book {
            width: 70px;
            height: 70px;
            margin: 0 auto 16px;
            border-radius: 22px;
            background: var(--main);
            display: grid;
            place-items: center;
            font-size: 36px;
            box-shadow: inset 0 0 0 2px rgba(45, 55, 72, 0.08);
        }

        .badge {
            display: inline-flex;
            border-radius: 999px;
            background: #FFF5F7;
            border: 1px solid var(--main);
            color: var(--text);
            font-weight: 800;
            padding: 10px 18px;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0;
            font-size: clamp(42px, 9vw, 84px);
            line-height: 1;
            letter-spacing: 0.02em;
        }

        .lead {
            margin: 28px auto 0;
            max-width: 680px;
            color: #718096;
            font-size: clamp(16px, 3.6vw, 22px);
            line-height: 2;
            font-weight: 700;
        }

        .note {
            margin-top: 34px;
            padding: 18px 22px;
            border-radius: 24px;
            background: var(--soft);
            color: #718096;
            font-size: 14px;
            line-height: 1.8;
            font-weight: 700;
        }

        .footer {
            margin-top: 24px;
            color: var(--sub);
            font-size: 13px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <div class="book">📖</div>
            <div class="logo">Oshi-Wiki</div>

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
</body>
</html>
