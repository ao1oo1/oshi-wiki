<?php
require __DIR__ . '/app/helpers.php';
page_header('OshiBaseについて', 'about');
?>
<h1>OshiBaseについて</h1>
<p>OshiBase は、推しの情報を「創作しやすい形」に整理するための創作支援データベースです。</p>

<section class="section">
  <h2 class="section-title">サービスの目的</h2>
  <div class="card">
    <p>二次創作・夢小説・一次創作の執筆時に必要になりやすい、一人称・二人称・呼称・口調・所属・関係性・世界観情報を、項目ごとに整理します。</p>
    <p>既存の百科事典型Wikiでは探しにくい「創作時に必要な情報」を中心に扱います。</p>
  </div>
</section>

<section class="section">
  <h2 class="section-title">OshiBaseで扱う情報</h2>
  <div class="card-grid">
    <div class="card"><h3>キャラクター情報</h3><p>名前、所属、年齢、身長、外見、性格、背景など。</p></div>
    <div class="card"><h3>口調・一人称</h3><p>一人称、二人称、語尾、敬語、感情別の話し方など。</p></div>
    <div class="card"><h3>呼称一覧</h3><p>「誰が誰を何と呼ぶか」を表形式で整理します。</p></div>
    <div class="card"><h3>用語・世界観</h3><p>地名、組織、能力、役職、重要なルールなど。</p></div>
  </div>
</section>

<section class="section">
  <h2 class="section-title">情報の区分</h2>
  <div class="card">
    <dl class="dl">
      <dt><?= reliability_badge('official') ?></dt><dd>公式資料・本編・公式サイトなどで確認できる事実情報。</dd>
      <dt><?= reliability_badge('sourced') ?></dt><dd>公式情報をもとに、自分の言葉で整理した要約。</dd>
      <dt><?= reliability_badge('theory') ?></dt><dd>解釈を含む考察。公式情報とは区別して表示します。</dd>
      <dt><?= reliability_badge('fan_memo') ?></dt><dd>創作用の補足メモ。事実としては扱いません。</dd>
    </dl>
  </div>
</section>

<section class="section">
  <h2 class="section-title">今後の展開</h2>
  <div class="card">
    <p>今後は、キュレーター制度・Discordコミュニティ・AI口調チェック・AI会話生成・個人用創作メモなどを段階的に追加していく想定です。</p>
    <p><a class="btn" href="<?= base_url('community.php') ?>">キュレーター募集を見る</a> <a class="btn btn-sub" href="<?= base_url('submit.php') ?>">情報提供する</a></p>
  </div>
</section>
<?php page_footer(); ?>
