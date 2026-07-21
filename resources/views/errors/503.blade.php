<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta name="robots" content="noindex, nofollow">
    <title>メンテナンス中 | Oshi-Wiki</title>
    <style>
        :root {
            color-scheme: light;
            --background: #fff8fa;
            --panel: #ffffff;
            --main: #fed7e2;
            --accent: #d95f82;
            --text: #2d3748;
            --subtext: #718096;
            --border: #f3d6de;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(
                    circle at top left,
                    rgba(254, 215, 226, 0.9),
                    transparent 42%
                ),
                linear-gradient(
                    160deg,
                    var(--background),
                    #ffffff
                );
            color: var(--text);
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Hiragino Kaku Gothic ProN",
                "Yu Gothic",
                "Meiryo",
                sans-serif;
        }

        .maintenance-card {
            width: min(100%, 720px);
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 32px;
            background: var(--panel);
            box-shadow:
                0 28px 70px rgba(45, 55, 72, 0.12);
        }

        .maintenance-bar {
            height: 12px;
            background:
                linear-gradient(
                    90deg,
                    var(--accent),
                    var(--main)
                );
        }

        .maintenance-content {
            padding: 48px 40px;
            text-align: center;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.03em;
        }

        .brand-mark {
            display: grid;
            width: 52px;
            height: 52px;
            place-items: center;
            border-radius: 18px;
            background: var(--main);
            font-size: 24px;
        }

        .status {
            display: inline-flex;
            margin-bottom: 20px;
            border-radius: 999px;
            background: #fff1f5;
            padding: 8px 16px;
            color: #a05a70;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.12em;
        }

        h1 {
            margin: 0;
            font-size: clamp(32px, 7vw, 52px);
            line-height: 1.3;
        }

        .lead {
            margin: 24px auto 0;
            max-width: 560px;
            color: var(--subtext);
            font-size: 16px;
            font-weight: 700;
            line-height: 2;
        }

        .notice {
            margin-top: 28px;
            border-radius: 20px;
            background: #f8fafc;
            padding: 18px;
            color: #4a5568;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.8;
        }

        .x-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            margin-top: 30px;
            border-radius: 18px;
            background: var(--text);
            padding: 14px 24px;
            color: #ffffff;
            font-weight: 800;
            text-decoration: none;
            transition:
                transform 0.15s ease,
                opacity 0.15s ease;
        }

        .x-link:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .apology {
            margin: 28px 0 0;
            color: var(--text);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.8;
        }

        @media (max-width: 640px) {
            body {
                padding: 16px;
            }

            .maintenance-card {
                border-radius: 24px;
            }

            .maintenance-content {
                padding: 36px 22px;
            }

            .brand {
                margin-bottom: 26px;
                font-size: 20px;
            }

            .lead {
                font-size: 15px;
            }

            .x-link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="maintenance-card">
        <div class="maintenance-bar"></div>

        <div class="maintenance-content">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">♡</span>
                <span>Oshi-Wiki</span>
            </div>

            <p class="status">MAINTENANCE</p>

            <h1>メンテナンス中</h1>

            <p class="lead">
                現在、サービスのメンテナンスを行っています。<br>
                詳細や再開予定については、公式Xのお知らせをご覧ください。
            </p>

            <div class="notice">
                メンテナンス開始前に保存されていなかった入力内容は、
                保持されない場合があります。
            </div>

            <a
                class="x-link"
                href="https://x.com/Oshi_Wiki"
                target="_blank"
                rel="noopener noreferrer"
            >
                公式Xのお知らせを見る
            </a>

            <p class="apology">
                ご不便をおかけして申し訳ございません。<br>
                メンテナンス終了まで、今しばらくお待ちください。
            </p>
        </div>
    </main>
</body>
</html>
