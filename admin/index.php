<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();

$counts = [];
foreach (['works', 'characters', 'appellations', 'terms', 'sources'] as $t) {
    $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
$pending = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'pending'")->fetchColumn();
$recent = $pdo->query("SELECT s.*, w.title AS work_title FROM submissions s LEFT JOIN works w ON w.id = s.work_id ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
$logs = $pdo->query("SELECT l.*, a.display_name FROM audit_logs l LEFT JOIN admin_users a ON a.id = l.user_id ORDER BY l.created_at DESC LIMIT 8")->fetchAll();

admin_header('ダッシュボード', 'dashboard');
?>
<div class="stat-grid">
  <div class="stat"><div class="num pink"><?= $pending ?></div><div class="label">承認待ち</div></div>
  <div class="stat"><div class="num"><?= $counts['works'] ?></div><div class="label">作品</div></div>
  <div class="stat"><div class="num"><?= $counts['characters'] ?></div><div class="label">キャラクター</div></div>
  <div class="stat"><div class="num"><?= $counts['appellations'] ?></div><div class="label">呼称</div></div>
  <div class="stat"><div class="num"><?= $counts['terms'] ?></div><div class="label">用語</div></div>
  <div class="stat"><div class="num"><?= $counts['sources'] ?></div><div class="label">出典</div></div>
</div>

<h2 class="section-title">最近のフォーム受付</h2>
<?php if (!$recent): ?><div class="card"><p class="muted">まだ受付はありません。</p></div>
<?php else: ?>
<div class="table-wrap">
  <table class="data">
    <tr><th>受付日</th><th>種別</th><th>対象作品</th><th>内容</th><th>ステータス</th></tr>
    <?php foreach ($recent as $r): ?>
    <tr class="<?= $r['status'] === 'pending' ? 'row-pending' : '' ?>">
      <td class="muted"><?= h(substr((string)$r['created_at'], 0, 16)) ?></td>
      <td><?= h(SUBMISSION_KIND[$r['kind']] ?? $r['kind']) ?></td>
      <td><?= h($r['work_title'] ?? '—') ?></td>
      <td><?= h(mb_strimwidth((string)$r['content'], 0, 80, '…')) ?></td>
      <td><?= submission_badge($r['status']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<p style="margin-top:12px"><a class="btn btn-sub btn-sm" href="<?= base_url('admin/submissions.php') ?>">フォーム受付一覧へ</a></p>
<?php endif; ?>

<h2 class="section-title" style="margin-top:36px">最近の編集履歴</h2>
<?php if (!$logs): ?><div class="card"><p class="muted">まだ編集履歴はありません。</p></div>
<?php else: ?>
<div class="table-wrap">
  <table class="data">
    <tr><th>日時</th><th>担当者</th><th>操作</th><th>内容</th></tr>
    <?php foreach ($logs as $l): ?>
    <tr>
      <td class="muted"><?= h(substr((string)$l['created_at'], 0, 16)) ?></td>
      <td><?= h($l['display_name'] ?? '—') ?></td>
      <td><span class="badge"><?= h($l['action']) ?></span></td>
      <td><?= h($l['summary']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>
<?php admin_footer(); ?>
