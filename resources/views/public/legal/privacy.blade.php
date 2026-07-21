@include('public.legal._layout_start', ['title' => 'プライバシーポリシー'])

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">1. 基本方針・適用範囲</h2>
    <p class="mt-3">
        Oshi-Wiki運営は、公開データベース、執筆補助ツール、有料プランその他Oshi-Wikiが提供する機能において取得する情報を、本ポリシーに従って取り扱います。
    </p>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">2. 取得する情報</h2>
    <div class="mt-3 space-y-3">
        <p><strong>公開ページ：</strong>アクセス日時、IPアドレスまたはIP由来のハッシュ、ブラウザ・端末情報、検索条件、参照元、アフィリエイトリンクのクリック情報。</p>
        <p><strong>アカウント：</strong>氏名または表示名、メールアドレス、認証情報、利用状況。</p>
        <p id="writing-tool"><strong>執筆ツール：</strong>オリジナルキャラクター、関係性、ストーリー本文、保存プロンプト、分析結果、アップロード画像等。</p>
        <p id="billing"><strong>有料プラン：</strong>Stripe顧客ID、契約ID、契約状態、請求周期、支払い結果。カード番号やセキュリティコードはOshi-Wikiでは保存しません。</p>
        <p><strong>お問い合わせ：</strong>問い合わせ内容、返信履歴、連絡先。</p>
    </div>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">3. 利用目的</h2>
    <p class="mt-3">
        サービス提供、本人確認、データ保存、契約管理、料金請求、不正利用防止、障害対応、問い合わせ対応、利用状況の分析、機能改善、法令対応のために利用します。
    </p>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">4. 執筆データの取扱い</h2>
    <p class="mt-3">
        執筆ツールに保存された創作データは、利用者本人が明示的に公開操作を行わない限り、公開データベースへ掲載しません。また、生成AIの学習用データとして利用しません。
    </p>
    <p class="mt-3">
        運営者は通常の運営において創作データの内容を閲覧しません。ただし、利用者からのサポート依頼、障害調査、不正利用対応、法令対応など必要かつ相当な場合に、権限を有する担当者が必要最小限の範囲で確認することがあります。
    </p>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">5. 外部サービス</h2>
    <p class="mt-3">
        決済にはStripeを利用する予定です。利用者がコピーしたプロンプトを外部の生成AIサービスへ入力した場合、その後の情報の取扱いには各サービスの規約・ポリシーが適用されます。
    </p>
</section>

<section id="affiliate">
    <h2 class="text-xl font-bold text-[#2D3748]">6. Cookie・アクセスログ・広告計測</h2>
    <p class="mt-3">
        ログイン状態の維持、セキュリティ、利用状況分析のためCookie等を使用します。アフィリエイトリンクのクリックでは、対象作品・商品・参照元等を記録します。生のIPアドレスをクリック履歴として保存せず、日単位のハッシュを利用します。
    </p>
</section>

<section id="data-retention">
    <h2 class="text-xl font-bold text-[#2D3748]">7. 保存期間・退会</h2>
    <p class="mt-3">
        サービス提供に必要な期間情報を保存します。サブスクの解約だけではアカウントと創作データは削除されません。アカウント削除時は、法令・会計・不正対策上の保持が必要な記録を除き、利用者データを削除します。
    </p>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">8. 第三者提供・委託</h2>
    <p class="mt-3">
        法令に基づく場合を除き、本人の同意なく個人情報を第三者へ提供しません。サービス運営に必要な範囲で、決済、メール、サーバー等の事業者へ取扱いを委託することがあります。
    </p>
</section>

<section>
    <h2 class="text-xl font-bold text-[#2D3748]">9. 安全管理・請求・変更</h2>
    <p class="mt-3">
        不正アクセス、漏えい、滅失等を防止するため必要な安全管理措置を講じます。開示・訂正・削除等の相談はお問い合わせ窓口へご連絡ください。本ポリシーを変更する場合は、重要な変更をサービス上で告知します。
    </p>
</section>

@include('public.legal._layout_end')
