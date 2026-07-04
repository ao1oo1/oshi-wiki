<?php
require __DIR__ . '/app/helpers.php';
page_header('利用規約・ガイドライン', 'terms');
?>
<h1>利用規約・掲載ガイドライン</h1>

<section class="section">
  <h2 class="section-title">サイトの方針</h2>
  <div class="card">
    <p>本サイトは、二次創作を行う方のためにキャラクター情報を整理する非公式のデータベースです。公式画像・漫画コマ・アニメスクリーンショット・セリフ全文・設定資料の転載を目的としません。</p>
    <p>掲載する情報は次の3種類に分け、明確に区別します。</p>
    <dl class="dl" style="margin-top:12px">
      <dt><?= reliability_badge('official') ?></dt><dd>公式資料・本編・公式サイトなどで確認できる事実情報。</dd>
      <dt><?= reliability_badge('sourced') ?></dt><dd>公式情報をもとに、運営・キュレーターが自分の言葉で整理した要約。</dd>
      <dt><?= reliability_badge('theory') ?></dt><dd>ユーザーやキュレーターの解釈を含む考察・創作メモ。公式情報とは区別して表示します。</dd>
    </dl>
  </div>
</section>

<section class="section">
  <h2 class="section-title">禁止事項</h2>
  <div class="card">
    <p>次の内容の投稿・登録はできません。</p>
    <p>公式画像の無断掲載、漫画コマの転載、アニメスクリーンショットの掲載、セリフ全文の大量掲載、公式設定資料の丸写し、ボイス全文の書き起こし、有料資料の転載、違法アップロード由来の情報の登録。</p>
  </div>
</section>

<section class="section">
  <h2 class="section-title">情報提供のお願い</h2>
  <div class="card">
    <p>情報を提供いただく際は、文章をご自身の言葉で要約し、出典（巻数・話数・公式サイトのURLなど）を明記してください。事実と考察は分けて記載をお願いします。作品ごとの公式二次創作ガイドラインがある場合は、そちらの確認もお願いします。</p>
  </div>
</section>

<section class="section">
  <h2 class="section-title">権利者の方へ</h2>
  <div class="card">
    <p>本サイトの掲載内容について削除をご希望の権利者の方は、<a href="<?= base_url('takedown.php') ?>"><b>削除依頼フォーム</b></a>からご連絡ください。確認のうえ速やかに対応いたします。</p>
  </div>
</section>
<?php page_footer(); ?>
