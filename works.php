<?php
require __DIR__ . '/app/helpers.php';
$works = db()->query("SELECT w.*, (SELECT COUNT(*) FROM characters c WHERE c.work_id = w.id) AS char_count FROM works w ORDER BY w.title_kana, w.title")->fetchAll();
page_header('作品一覧', 'works');
?>
<h1>作品一覧</h1>
<p class="muted">登録作品：<?= count($works) ?>件</p>
<div class="card-grid" style="margin-top:20px">
<?php foreach ($works as $w): ?>
  <a class="card" href="<?= base_url('work.php?id=' . $w['id']) ?>">
    <h3><?= h($w['title']) ?></h3>
    <div class="kana"><?= h($w['title_kana']) ?></div>
    <div class="meta"><?= h($w['genre']) ?> · キャラ<?= (int)$w['char_count'] ?>名 <?= status_badge($w['status']) ?></div>
  </a>
<?php endforeach; ?>
<?php if (!$works): ?><div class="card"><p>まだ作品が登録されていません。</p></div><?php endif; ?>
</div>
<?php page_footer(); ?>
