<?php
require __DIR__ . '/app/helpers.php';
page_header('キュレーター募集', 'community');
?>
<h1>キュレーター・支援者募集</h1>
<p>OshiBaseでは、作品ごとの情報整理を手伝ってくださるキュレーター・支援者を募集しています。</p>

<section class="section">
  <h2 class="section-title">参加方法</h2>
  <div class="card-grid">
    <div class="card"><h3>情報提供</h3><p>ログイン不要で、フォームから一人称・呼称・口調・出典などを送信できます。</p><p><a class="btn btn-sm" href="<?= base_url('submit.php') ?>">情報提供する</a></p></div>
    <div class="card"><h3>修正依頼</h3><p>掲載内容の誤りや出典の不足を見つけた場合は、修正依頼を送信できます。</p></div>
    <div class="card"><h3>キュレーター</h3><p>担当作品を持ち、承認制でキャラクター・呼称・用語を整理する役割です。</p></div>
  </div>
</section>

<section class="section">
  <h2 class="section-title">キュレーターの役割</h2>
  <div class="card">
    <ul>
      <li>担当作品のキャラクター情報を整理する</li>
      <li>一人称・呼称・口調情報に出典を添える</li>
      <li>情報提供・修正依頼を確認する</li>
      <li>公式情報・要約・考察を混同しないよう管理する</li>
      <li>公式画像・セリフ全文・設定資料の転載を避ける</li>
    </ul>
  </div>
</section>

<section class="section">
  <h2 class="section-title">Discordコミュニティ構想</h2>
  <div class="card">
    <p>今後、作品別チャンネル・情報提供チャンネル・修正依頼チャンネル・AI機能テストチャンネルなどを設ける予定です。</p>
    <p class="muted">Discord招待URLは未設定です。公開時にこのページへ掲載してください。</p>
  </div>
</section>
<?php page_footer(); ?>
