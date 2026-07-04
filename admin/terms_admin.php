<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$filterWork = (int)($_GET['work_id'] ?? 0);

$works = $pdo->query('SELECT id, title FROM works ORDER BY title_kana, title')->fetchAll();
$workOptions = [];
foreach ($works as $w) $workOptions[$w['id']] = $w['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $post = $_POST['do'] ?? '';
    if ($post === 'save') {
        require_login('curator');
        $workId = (int)($_POST['work_id'] ?? 0);
        require_work_editable($u, $workId);
        $name = trim((string)($_POST['name'] ?? ''));
        if (!$workId || $name === '') {
            flash('作品と用語名は必須です。', 'error');
        } else {
            $type = array_key_exists($_POST['type'] ?? '', TERM_TYPES) ? $_POST['type'] : 'other';
            $rel = array_key_exists($_POST['reliability'] ?? '', RELIABILITY) ? $_POST['reliability'] : 'unverified';
            $data = [$workId, $name, trim((string)($_POST['name_kana'] ?? '')), $type,
                     trim((string)($_POST['description'] ?? '')), trim((string)($_POST['source_note'] ?? '')), $rel];
            if ($id) {
                $pdo->prepare('UPDATE terms SET work_id=?, name=?, name_kana=?, type=?, description=?, source_note=?, reliability=? WHERE id=?')
                    ->execute([...$data, $id]);
                audit('update', 'term', $id, '用語「' . $name . '」を更新');
                flash('用語を更新しました。');
            } else {
                $pdo->prepare('INSERT INTO terms (work_id,name,name_kana,type,description,source_note,reliability) VALUES (?,?,?,?,?,?,?)')->execute($data);
                audit('create', 'term', (int)$pdo->lastInsertId(), '用語「' . $name . '」を登録');
                flash('用語を登録しました。');
            }
            redirect('admin/terms_admin.php?work_id=' . $workId);
        }
    }
    if ($post === 'delete') {
        require_login('curator');
        $st = $pdo->prepare('SELECT * FROM terms WHERE id = ?');
        $st->execute([$id]);
        $t = $st->fetch();
        if ($t) {
            require_work_editable($u, (int)$t['work_id']);
            $pdo->prepare('DELETE FROM terms WHERE id = ?')->execute([$id]);
            audit('delete', 'term', $id, '用語「' . $t['name'] . '」を削除');
            flash('用語を削除しました。');
        }
        redirect('admin/terms_admin.php');
    }
}

$action = $_GET['action'] ?? 'list';
if ($action === 'edit' || $action === 'new') {
    require_login('curator');
    $row = ['work_id' => $filterWork ?: ($works[0]['id'] ?? 0), 'name' => '', 'name_kana' => '', 'type' => 'other', 'description' => '', 'source_note' => '', 'reliability' => 'unverified'];
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM terms WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch() ?: $row;
    }
    admin_header($id ? '用語を編集' : '用語を登録', 'terms');
    ?>
    <div class="form-card">
      <form method="post" action="<?= base_url('admin/terms_admin.php?action=edit' . ($id ? '&id=' . $id : '')) ?>">
        <?= csrf_field() ?><input type="hidden" name="do" value="save">
        <div class="form-grid">
          <label class="field">作品 <span class="req">必須</span><select name="work_id"><?= select_options($workOptions, (string)$row['work_id']) ?></select></label>
          <label class="field">種別<select name="type"><?= select_options(TERM_TYPES, $row['type']) ?></select></label>
          <label class="field">用語名 <span class="req">必須</span><input type="text" name="name" value="<?= h($row['name']) ?>" required></label>
          <label class="field">読み仮名<input type="text" name="name_kana" value="<?= h($row['name_kana']) ?>"></label>
          <label class="field full">説明<textarea name="description"><?= h($row['description']) ?></textarea></label>
          <label class="field">出典<input type="text" name="source_note" value="<?= h($row['source_note']) ?>"></label>
          <label class="field">信頼度<select name="reliability"><?= select_options(RELIABILITY, $row['reliability']) ?></select></label>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit"><?= $id ? '更新する' : '登録する' ?></button>
          <a class="btn btn-sub" href="<?= base_url('admin/terms_admin.php') ?>">一覧に戻る</a>
        </div>
      </form>
    </div>
    <?php
    admin_footer();
    exit;
}

$sql = 'SELECT t.*, w.title AS work_title FROM terms t JOIN works w ON w.id = t.work_id';
$params = [];
if ($filterWork) { $sql .= ' WHERE t.work_id = ?'; $params[] = $filterWork; }
$sql .= ' ORDER BY w.title_kana, t.name_kana, t.name';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

admin_header('用語管理', 'terms');
?>
<div class="toolbar">
  <form method="get" action="<?= base_url('admin/terms_admin.php') ?>">
    <select name="work_id" onchange="this.form.submit()"><?= select_options(['' => 'すべての作品'] + $workOptions, (string)($filterWork ?: '')) ?></select>
  </form>
  <?php if (has_role('curator')): ?>
  <a class="btn" href="<?= base_url('admin/terms_admin.php?action=new' . ($filterWork ? '&work_id=' . $filterWork : '')) ?>">＋ 用語を登録</a>
  <?php endif; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <tr><th>用語</th><th>種別</th><th>作品</th><th>説明</th><th>信頼度</th><th></th></tr>
    <?php foreach ($rows as $t): ?>
    <tr>
      <td><b><?= h($t['name']) ?></b><br><span class="muted"><?= h($t['name_kana']) ?></span></td>
      <td><span class="badge"><?= h(TERM_TYPES[$t['type']] ?? $t['type']) ?></span></td>
      <td class="muted"><?= h($t['work_title']) ?></td>
      <td><?= h(mb_strimwidth((string)$t['description'], 0, 80, '…')) ?></td>
      <td><?= reliability_badge($t['reliability']) ?></td>
      <td style="white-space:nowrap">
        <?php if (can_edit_work($u, (int)$t['work_id'])): ?>
        <a class="btn btn-sub btn-sm" href="<?= base_url('admin/terms_admin.php?action=edit&id=' . $t['id']) ?>">編集</a>
        <form method="post" action="<?= base_url('admin/terms_admin.php?id=' . $t['id']) ?>" style="display:inline" onsubmit="return confirm('この用語を削除しますか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">削除</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="muted">用語が登録されていません。</td></tr><?php endif; ?>
  </table>
</div>
<?php admin_footer(); ?>
