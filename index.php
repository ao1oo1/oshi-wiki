<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();

$works = $pdo->query("SELECT * FROM works ORDER BY updated_at DESC LIMIT 6")->fetchAll();
$chars = $pdo->query("SELECT c.*, w.title AS work_title FROM characters c JOIN works w ON w.id = c.work_id ORDER BY c.created_at DESC LIMIT 8")->fetchAll();

page_header('トップ', 'home');
?>
<div class="hero">
  <h1><span class="accent">推しの口調</span>、もう迷わない。</h1>
  <p class="lead">一人称・呼称・口調・関係性を整理する、二次創作のためのキャラクター情報データベース。</p>
  <form class="search-box" action="<?= base_url('search.php') ?>" method="get">
    <input type="text" name="q" placeholder="キャラ名・一人称・呼称で検索" autocomplete="off">
    <button type="submit">検索</button>
  </form>
  <p class="search-hint">例：「俺」で一人称検索、「先輩」で呼称検索、作品名・用語もまとめて探せます</p>
</div>

<section class="section">
  <h2 class="section-title">作品から探す</h2>
  <?php if (!$works): ?>
    <div class="card"><p>まだ作品が登録されていません。管理画面から最初の作品を登録しましょう。</p></div>
  <?php else: ?>
  <div class="card-grid">
    <?php foreach ($works as $w): ?>
    <a class="card" href="<?= base_url('work.php?id=' . $w['id']) ?>">
      <h3><?= h($w['title']) ?></h3>
      <div class="kana"><?= h($w['title_kana']) ?></div>
      <div class="meta"><?= h($w['genre']) ?> <?= status_badge($w['status']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <p style="margin-top:16px"><a class="btn btn-sub" href="<?= base_url('works.php') ?>">作品一覧をすべて見る</a></p>
  <?php endif; ?>
</section>

<section class="section">
  <h2 class="section-title">最近追加されたキャラクター</h2>
  <?php if (!$chars): ?>
    <div class="card"><p>まだキャラクターが登録されていません。</p></div>
  <?php else: ?>
  <div class="card-grid">
    <?php foreach ($chars as $c): ?>
    <a class="card" href="<?= base_url('character.php?id=' . $c['id']) ?>">
      <h3><?= h($c['name']) ?></h3>
      <div class="kana"><?= h($c['name_kana']) ?></div>
      <div class="meta"><?= h($c['work_title']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<section class="section">
  <h2 class="section-title">このサイトについて</h2>
  <div class="card">
    <p>OshiBase は、二次創作・夢小説を書く人のための創作支援データベースです。キャラクターの一人称・二人称・呼称・口調・関係性を、出典付きで整理しています。</p>
    <p>一般ユーザーはログイン不要で閲覧・検索・情報提供ができます。管理者・キュレーターのみログインし、承認制でデータを反映します。</p>
    <p>公式画像やセリフ全文の転載は行いません。掲載内容は公式情報の要約と、公式情報と明確に区別された考察・創作メモで構成されます。詳しくは<a href="<?= base_url('terms.php') ?>"><b>ガイドライン</b></a>をご覧ください。</p>
    <p><a class="btn btn-sm" href="<?= base_url('about.php') ?>">OshiBaseについて</a> <a class="btn btn-sub btn-sm" href="<?= base_url('community.php') ?>">キュレーター募集</a></p>
  </div>
</section>
<?php page_footer(); ?>
