<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();
$filter = $_GET['status'] ?? 'pending';
if (!in_array($filter, ['all', 'pending', 'reviewed', 'approved', 'rejected'], true)) $filter = 'pending';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $to = $_POST['to'] ?? '';
    $note = trim((string)($_POST['review_note'] ?? ''));

    // 確認済み(reviewed)にする：Reviewer以上 / 反映・見送り：Curator以上
    if ($to === 'reviewed') require_login('reviewer');
    elseif ($to === 'approved' || $to === 'rejected') require_login('curator');
    else { flash('不正な操作です。', 'error'); redirect('admin/submissions.php'); }

    $pdo->prepare('UPDATE submissions SET status = ?, review_note = ?, reviewed_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
        ->execute([$to, $note, $u['id'], $id]);
    audit($to === 'reviewed' ? 'review' : ($to === 'approved' ? 'approve' : 'reject'), 'submission', $id,
        'フォーム受付 #' . $id . ' を「' . (SUBMISSION_STATUS[$to] ?? $to) . '」に変更');
    flash('ステータスを「' . (SUBMISSION_STATUS[$to] ?? $to) . '」に変更しました。');
    redirect('admin/submissions.php?status=' . $filter);
}

$sql = 'SELECT s.*, w.title AS work_title, c.name AS char_name, a.display_name AS reviewer_name
        FROM submissions s
        LEFT JOIN works w ON w.id = s.work_id
        LEFT JOIN characters c ON c.id = s.character_id
        LEFT JOIN admin_users a ON a.id = s.reviewed_by';
$params = [];
if ($filter !== 'all') { $sql .= ' WHERE s.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY s.created_at DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

admin_header('フォーム受付（情報提供・修正・削除依頼）', 'submissions');
?>
<div class="toolbar">
  <nav>
    <?php foreach (['pending' => '承認待ち', 'reviewed' => '確認済み', 'approved' => '反映済み', 'rejected' => '見送り', 'all' => 'すべて'] as $k => $label): ?>
      <a class="btn btn-sm <?= $filter === $k ? '' : 'btn-sub' ?>" href="<?= base_url('admin/submissions.php?status=' . $k) ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </nav>
</div>

<p class="muted">承認フロー：承認待ち →（Reviewer以上が）確認済み →（Curator以上が）反映済み または 見送り。反映は各管理ページで手動編集のうえ、ここでステータスを更新してください。</p>

<?php if (!$rows): ?><div class="card"><p class="muted">該当する受付はありません。</p></div><?php endif; ?>

<?php foreach ($rows as $r): ?>
<div class="card" style="margin-bottom:16px">
  <p style="margin:0 0 8px">
    <span class="badge"><?= h(SUBMISSION_KIND[$r['kind']] ?? $r['kind']) ?></span>
    <span class="badge"><?= h(INFO_TYPES[$r['info_type'] ?? 'sourced'] ?? ($r['info_type'] ?? 'sourced')) ?></span>
    <?= submission_badge($r['status']) ?>
    <span class="muted"> #<?= (int)$r['id'] ?> · <?= h(substr((string)$r['created_at'], 0, 16)) ?></span>
  </p>
  <p style="margin:0 0 6px">
    <b>対象：</b><?= h($r['work_title'] ?? '（作品指定なし）') ?><?= $r['char_name'] ? ' / ' . h($r['char_name']) : '' ?>
    <?php if ($r['submitter_name']): ?> · <b>提供者：</b><?= h($r['submitter_name']) ?><?php endif; ?>
    <?php if ($r['submitter_contact'] && has_role('reviewer')): ?> <span class="muted">（連絡先：<?= h($r['submitter_contact']) ?>）</span><?php endif; ?>
  </p>
  <p style="margin:0 0 6px; white-space:pre-wrap"><?= h($r['content']) ?></p>
  <?php if ($r['source_note']): ?><p class="muted" style="margin:0 0 6px">出典：<?= h($r['source_note']) ?></p><?php endif; ?>
  <?php if ($r['review_note']): ?><p class="muted" style="margin:0 0 6px">対応メモ：<?= h($r['review_note']) ?>（<?= h($r['reviewer_name'] ?? '') ?>）</p><?php endif; ?>

  <?php if (in_array($r['status'], ['pending', 'reviewed'], true) && has_role('reviewer')): ?>
  <form method="post" action="<?= base_url('admin/submissions.php?status=' . $filter) ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:10px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
    <input type="text" name="review_note" placeholder="対応メモ（任意）" style="flex:1;min-width:200px;margin-top:0">
    <?php if ($r['status'] === 'pending'): ?>
      <button class="btn btn-sub btn-sm" name="to" value="reviewed" type="submit">確認済みにする</button>
    <?php endif; ?>
    <?php if (has_role('curator')): ?>
      <button class="btn btn-sm" name="to" value="approved" type="submit">反映済みにする</button>
      <button class="btn btn-danger btn-sm" name="to" value="rejected" type="submit">見送りにする</button>
    <?php endif; ?>
  </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php admin_footer(); ?>
