<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM works WHERE id = ?');
$st->execute([$id]);
$work = $st->fetch();
if (!$work) { http_response_code(404); page_header('作品が見つかりません'); echo '<div class="card"><p>お探しの作品は見つかりませんでした。</p></div>'; page_footer(); exit; }

$st = $pdo->prepare('SELECT * FROM characters WHERE work_id = ? ORDER BY name_kana, name');
$st->execute([$id]);
$chars = $st->fetchAll();
$charNames = [];
foreach ($chars as $c) $charNames[$c['id']] = $c['name'];

$st = $pdo->prepare('SELECT * FROM appellations WHERE work_id = ? ORDER BY from_character_id, to_character_id');
$st->execute([$id]);
$apps = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM terms WHERE work_id = ? ORDER BY name_kana, name');
$st->execute([$id]);
$terms = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM sources WHERE work_id = ? ORDER BY id');
$st->execute([$id]);
$sources = $st->fetchAll();

page_header($work['title'], 'works');
?>
<p class="muted"><a href="<?= base_url('works.php') ?>">作品一覧</a> / <?= h($work['title']) ?></p>
<h1><?= h($work['title']) ?> <?= status_badge($work['status']) ?></h1>
<p class="muted"><?= h($work['title_kana']) ?> · <?= h($work['genre']) ?> · <?= h($work['medium']) ?></p>

<div class="card" style="margin-top:16px">
  <p><?= nl2p($work['description']) ?></p>
  <?php if (trim((string)$work['caution']) !== ''): ?>
    <p class="muted">注意事項：<?= nl2br(h($work['caution'])) ?></p>
  <?php endif; ?>
  <p class="muted">
    <?php if ($work['official_url']): ?><a href="<?= h($work['official_url']) ?>" target="_blank" rel="noopener">公式サイト</a><?php endif; ?>
    <?php if ($work['guideline_url']): ?> · <a href="<?= h($work['guideline_url']) ?>" target="_blank" rel="noopener">二次創作ガイドライン</a><?php endif; ?>
  </p>
</div>

<section class="section">
  <h2 class="section-title">登場キャラクター</h2>
  <div class="card-grid">
  <?php foreach ($chars as $c): ?>
    <a class="card" href="<?= base_url('character.php?id=' . $c['id']) ?>">
      <h3><?= h($c['name']) ?></h3>
      <div class="kana"><?= h($c['name_kana']) ?></div>
      <div class="meta"><?= h($c['affiliation']) ?><?= $c['role'] ? ' / ' . h($c['role']) : '' ?></div>
    </a>
  <?php endforeach; ?>
  <?php if (!$chars): ?><div class="card"><p>キャラクターは未登録です。</p></div><?php endif; ?>
  </div>
</section>

<?php if ($apps): ?>
<section class="section">
  <h2 class="section-title">呼称一覧</h2>
  <div class="table-wrap">
    <table class="data">
      <tr><th>呼ぶ側</th><th>呼ばれる側</th><th>呼称</th><th>場面</th><th>出典</th><th>信頼度</th></tr>
      <?php foreach ($apps as $a): ?>
      <tr>
        <td><a href="<?= base_url('character.php?id=' . $a['from_character_id']) ?>"><?= h($charNames[$a['from_character_id']] ?? '?') ?></a></td>
        <td><a href="<?= base_url('character.php?id=' . $a['to_character_id']) ?>"><?= h($charNames[$a['to_character_id']] ?? '?') ?></a></td>
        <td><span class="appellation-cell"><?= h($a['appellation']) ?></span></td>
        <td><?= h($a['scene']) ?></td>
        <td class="muted"><?= h($a['source_note']) ?></td>
        <td><?= reliability_badge($a['reliability']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>
<?php endif; ?>

<?php if ($terms): ?>
<section class="section">
  <h2 class="section-title">用語一覧</h2>
  <div class="table-wrap">
    <table class="data">
      <tr><th>用語</th><th>種別</th><th>説明</th><th>出典</th></tr>
      <?php foreach ($terms as $t): ?>
      <tr>
        <td><b><?= h($t['name']) ?></b><br><span class="muted"><?= h($t['name_kana']) ?></span></td>
        <td><span class="badge"><?= h(TERM_TYPES[$t['type']] ?? $t['type']) ?></span></td>
        <td><?= nl2br(h($t['description'])) ?></td>
        <td class="muted"><?= h($t['source_note']) ?> <?= reliability_badge($t['reliability']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>
<?php endif; ?>

<?php if ($sources): ?>
<section class="section">
  <h2 class="section-title">出典一覧</h2>
  <div class="table-wrap">
    <table class="data">
      <tr><th>種別</th><th>タイトル</th><th>巻・話</th><th>URL</th><th>確認日</th></tr>
      <?php foreach ($sources as $s): ?>
      <tr>
        <td><span class="badge"><?= h(SOURCE_TYPES[$s['source_type']] ?? $s['source_type']) ?></span></td>
        <td><?= h($s['title']) ?></td>
        <td><?= h(trim($s['volume'] . ' ' . $s['episode'] . ' ' . $s['page'])) ?></td>
        <td><?php if ($s['url']): ?><a href="<?= h($s['url']) ?>" target="_blank" rel="noopener">リンク</a><?php endif; ?></td>
        <td class="muted"><?= h($s['checked_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="card">
    <p>この作品の情報に追加・修正があれば、<a href="<?= base_url('submit.php?work_id=' . $work['id']) ?>"><b>情報提供フォーム</b></a>からお知らせください。出典を添えていただけると確認がスムーズです。</p>
  </div>
</section>
<?php page_footer(); ?>
