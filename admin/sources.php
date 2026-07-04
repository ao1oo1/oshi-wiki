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
        $title = trim((string)($_POST['title'] ?? ''));
        if (!$workId || $title === '') {
            flash('作品とタイトルは必須です。', 'error');
        } else {
            $type = array_key_exists($_POST['source_type'] ?? '', SOURCE_TYPES) ? $_POST['source_type'] : 'other';
            $data = [$workId, $type, $title,
                trim((string)($_POST['volume'] ?? '')), trim((string)($_POST['episode'] ?? '')),
                trim((string)($_POST['page'] ?? '')), trim((string)($_POST['url'] ?? '')),
                trim((string)($_POST['checked_at'] ?? '')), trim((string)($_POST['note'] ?? ''))];
            if ($id) {
                $pdo->prepare('UPDATE sources SET work_id=?, source_type=?, title=?, volume=?, episode=?, page=?, url=?, checked_at=?, note=? WHERE id=?')
                    ->execute([...$data, $id]);
                audit('update', 'source', $id, '出典「' . $title . '」を更新');
                flash('出典を更新しました。');
            } else {
                $pdo->prepare('INSERT INTO sources (work_id,source_type,title,volume,episode,page,url,checked_at,note) VALUES (?,?,?,?,?,?,?,?,?)')->execute($data);
                audit('create', 'source', (int)$pdo->lastInsertId(), '出典「' . $title . '」を登録');
                flash('出典を登録しました。');
            }
            redirect('admin/sources.php?work_id=' . $workId);
        }
    }
    if ($post === 'delete') {
        require_login('curator');
        $st = $pdo->prepare('SELECT * FROM sources WHERE id = ?');
        $st->execute([$id]);
        $s = $st->fetch();
        if ($s) {
            require_work_editable($u, (int)$s['work_id']);
            $pdo->prepare('DELETE FROM sources WHERE id = ?')->execute([$id]);
            audit('delete', 'source', $id, '出典「' . $s['title'] . '」を削除');
            flash('出典を削除しました。');
        }
        redirect('admin/sources.php');
    }
}

$action = $_GET['action'] ?? 'list';
if ($action === 'edit' || $action === 'new') {
    require_login('curator');
    $row = ['work_id' => $filterWork ?: ($works[0]['id'] ?? 0), 'source_type' => 'manga', 'title' => '', 'volume' => '', 'episode' => '', 'page' => '', 'url' => '', 'checked_at' => date('Y-m-d'), 'note' => ''];
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM sources WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch() ?: $row;
    }
    admin_header($id ? '出典を編集' : '出典を登録', 'sources');
    ?>
    <div class="form-card">
      <form method="post" action="<?= base_url('admin/sources.php?action=edit' . ($id ? '&id=' . $id : '')) ?>">
        <?= csrf_field() ?><input type="hidden" name="do" value="save">
        <div class="form-grid">
          <label class="field">作品 <span class="req">必須</span><select name="work_id"><?= select_options($workOptions, (string)$row['work_id']) ?></select></label>
          <label class="field">出典種別<select name="source_type"><?= select_options(SOURCE_TYPES, $row['source_type']) ?></select></label>
          <label class="field full">タイトル <span class="req">必須</span><input type="text" name="title" value="<?= h($row['title']) ?>" required placeholder="例：原作第2巻 / アニメ第3話「〇〇」"></label>
          <label class="field">巻数<input type="text" name="volume" value="<?= h($row['volume']) ?>"></label>
          <label class="field">話数・エピソード<input type="text" name="episode" value="<?= h($row['episode']) ?>"></label>
          <label class="field">ページ<input type="text" name="page" value="<?= h($row['page']) ?>"></label>
          <label class="field">確認日<input type="text" name="checked_at" value="<?= h($row['checked_at']) ?>" placeholder="YYYY-MM-DD"></label>
          <label class="field full">URL<input type="url" name="url" value="<?= h($row['url']) ?>"></label>
          <label class="field full">メモ<textarea name="note"><?= h($row['note']) ?></textarea></label>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit"><?= $id ? '更新する' : '登録する' ?></button>
          <a class="btn btn-sub" href="<?= base_url('admin/sources.php') ?>">一覧に戻る</a>
        </div>
      </form>
    </div>
    <?php
    admin_footer();
    exit;
}

$sql = 'SELECT s.*, w.title AS work_title FROM sources s JOIN works w ON w.id = s.work_id';
$params = [];
if ($filterWork) { $sql .= ' WHERE s.work_id = ?'; $params[] = $filterWork; }
$sql .= ' ORDER BY w.title_kana, s.id';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

admin_header('出典管理', 'sources');
?>
<div class="toolbar">
  <form method="get" action="<?= base_url('admin/sources.php') ?>">
    <select name="work_id" onchange="this.form.submit()"><?= select_options(['' => 'すべての作品'] + $workOptions, (string)($filterWork ?: '')) ?></select>
  </form>
  <?php if (has_role('curator')): ?>
  <a class="btn" href="<?= base_url('admin/sources.php?action=new' . ($filterWork ? '&work_id=' . $filterWork : '')) ?>">＋ 出典を登録</a>
  <?php endif; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <tr><th>種別</th><th>タイトル</th><th>巻・話</th><th>作品</th><th>確認日</th><th></th></tr>
    <?php foreach ($rows as $s): ?>
    <tr>
      <td><span class="badge"><?= h(SOURCE_TYPES[$s['source_type']] ?? $s['source_type']) ?></span></td>
      <td><?= h($s['title']) ?><?php if ($s['url']): ?><br><a class="muted" href="<?= h($s['url']) ?>" target="_blank" rel="noopener"><?= h(mb_strimwidth($s['url'], 0, 40, '…')) ?></a><?php endif; ?></td>
      <td><?= h(trim($s['volume'] . ' ' . $s['episode'] . ' ' . $s['page'])) ?></td>
      <td class="muted"><?= h($s['work_title']) ?></td>
      <td class="muted"><?= h($s['checked_at']) ?></td>
      <td style="white-space:nowrap">
        <?php if (can_edit_work($u, (int)$s['work_id'])): ?>
        <a class="btn btn-sub btn-sm" href="<?= base_url('admin/sources.php?action=edit&id=' . $s['id']) ?>">編集</a>
        <form method="post" action="<?= base_url('admin/sources.php?id=' . $s['id']) ?>" style="display:inline" onsubmit="return confirm('この出典を削除しますか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">削除</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="muted">出典が登録されていません。</td></tr><?php endif; ?>
  </table>
</div>
<?php admin_footer(); ?>
