<?php
require __DIR__ . '/../app/helpers.php';
require_login('viewer');
$pdo = db();

$page = max(1, (int)($_GET['page'] ?? 1));
$per = 50;
$total = (int)$pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
$st = $pdo->prepare('SELECT l.*, a.display_name FROM audit_logs l LEFT JOIN admin_users a ON a.id = l.user_id ORDER BY l.created_at DESC, l.id DESC LIMIT ? OFFSET ?');
$st->bindValue(1, $per, PDO::PARAM_INT);
$st->bindValue(2, ($page - 1) * $per, PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();
$pages = max(1, (int)ceil($total / $per));

admin_header('編集履歴', 'logs');
?>
<p class="muted">全<?= $total ?>件（管理画面での作成・更新・削除・承認操作を記録しています）</p>
<div class="table-wrap">
  <table class="data">
    <tr><th>日時</th><th>担当者</th><th>操作</th><th>対象</th><th>内容</th></tr>
    <?php foreach ($rows as $l): ?>
    <tr>
      <td class="muted"><?= h(substr((string)$l['created_at'], 0, 16)) ?></td>
      <td><?= h($l['display_name'] ?? '—') ?></td>
      <td><span class="badge"><?= h($l['action']) ?></span></td>
      <td class="muted"><?= h($l['target_type']) ?> #<?= h((string)$l['target_id']) ?></td>
      <td><?= h($l['summary']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="muted">履歴はまだありません。</td></tr><?php endif; ?>
  </table>
</div>
<?php if ($pages > 1): ?>
<p style="margin-top:14px">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="btn btn-sm <?= $i === $page ? '' : 'btn-sub' ?>" href="<?= base_url('admin/logs.php?page=' . $i) ?>"><?= $i ?></a>
  <?php endfor; ?>
</p>
<?php endif; ?>
<?php admin_footer(); ?>
