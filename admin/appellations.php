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
        $from = (int)($_POST['from_character_id'] ?? 0);
        $to = (int)($_POST['to_character_id'] ?? 0);
        $app = trim((string)($_POST['appellation'] ?? ''));
        if (!$workId || !$from || !$to || $app === '') {
            flash('作品・呼ぶ側・呼ばれる側・呼称は必須です。', 'error');
        } else {
            $rel = array_key_exists($_POST['reliability'] ?? '', RELIABILITY) ? $_POST['reliability'] : 'unverified';
            $data = [$workId, $from, $to, $app,
                trim((string)($_POST['scene'] ?? '')), trim((string)($_POST['note'] ?? '')),
                trim((string)($_POST['source_note'] ?? '')), $rel];
            if ($id) {
                $pdo->prepare('UPDATE appellations SET work_id=?, from_character_id=?, to_character_id=?, appellation=?, scene=?, note=?, source_note=?, reliability=? WHERE id=?')
                    ->execute([...$data, $id]);
                audit('update', 'appellation', $id, '呼称「' . $app . '」を更新');
                flash('呼称を更新しました。');
            } else {
                $pdo->prepare('INSERT INTO appellations (work_id, from_character_id, to_character_id, appellation, scene, note, source_note, reliability) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute($data);
                audit('create', 'appellation', (int)$pdo->lastInsertId(), '呼称「' . $app . '」を登録');
                flash('呼称を登録しました。');
            }
            redirect('admin/appellations.php?work_id=' . $workId);
        }
    }

    if ($post === 'delete') {
        require_login('curator');
        $st = $pdo->prepare('SELECT * FROM appellations WHERE id = ?');
        $st->execute([$id]);
        $a = $st->fetch();
        if ($a) {
            require_work_editable($u, (int)$a['work_id']);
            $pdo->prepare('DELETE FROM appellations WHERE id = ?')->execute([$id]);
            audit('delete', 'appellation', $id, '呼称「' . $a['appellation'] . '」を削除');
            flash('呼称を削除しました。');
        }
        redirect('admin/appellations.php');
    }
}

$action = $_GET['action'] ?? 'list';
if ($action === 'edit' || $action === 'new') {
    require_login('curator');
    $row = ['work_id' => $filterWork ?: ($works[0]['id'] ?? 0), 'from_character_id' => 0, 'to_character_id' => 0,
            'appellation' => '', 'scene' => '', 'note' => '', 'source_note' => '', 'reliability' => 'unverified'];
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM appellations WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch() ?: $row;
    }
    $st = $pdo->prepare('SELECT id, name FROM characters WHERE work_id = ? ORDER BY name_kana');
    $st->execute([(int)$row['work_id']]);
    $charOptions = ['' => '（選択）'];
    foreach ($st->fetchAll() as $c) $charOptions[$c['id']] = $c['name'];

    admin_header($id ? '呼称を編集' : '呼称を登録', 'appellations');
    ?>
    <div class="form-card">
      <form method="post" action="<?= base_url('admin/appellations.php?action=edit' . ($id ? '&id=' . $id : '')) ?>">
        <?= csrf_field() ?><input type="hidden" name="do" value="save">
        <div class="form-grid">
          <label class="field full">作品（変更すると開き直します）
            <select name="work_id" onchange="location.href='<?= base_url('admin/appellations.php?action=new') ?>&work_id='+this.value">
              <?= select_options($workOptions, (string)$row['work_id']) ?>
            </select>
          </label>
          <label class="field">呼ぶ側 <span class="req">必須</span><select name="from_character_id" required><?= select_options($charOptions, (string)$row['from_character_id']) ?></select></label>
          <label class="field">呼ばれる側 <span class="req">必須</span><select name="to_character_id" required><?= select_options($charOptions, (string)$row['to_character_id']) ?></select></label>
          <label class="field">呼称 <span class="req">必須</span><input type="text" name="appellation" value="<?= h($row['appellation']) ?>" required placeholder="例：先輩 / お前 / 〇〇くん"></label>
          <label class="field">場面<input type="text" name="scene" value="<?= h($row['scene']) ?>" placeholder="例：通常時 / 戦闘時 / 公の場"></label>
          <label class="field full">備考<input type="text" name="note" value="<?= h($row['note']) ?>"></label>
          <label class="field">出典<input type="text" name="source_note" value="<?= h($row['source_note']) ?>" placeholder="例：アニメ第3話"></label>
          <label class="field">信頼度<select name="reliability"><?= select_options(RELIABILITY, $row['reliability']) ?></select></label>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit"><?= $id ? '更新する' : '登録する' ?></button>
          <a class="btn btn-sub" href="<?= base_url('admin/appellations.php') ?>">一覧に戻る</a>
        </div>
      </form>
    </div>
    <?php
    admin_footer();
    exit;
}

$sql = "SELECT a.*, w.title AS work_title, cf.name AS from_name, ct.name AS to_name
        FROM appellations a JOIN works w ON w.id = a.work_id
        JOIN characters cf ON cf.id = a.from_character_id
        JOIN characters ct ON ct.id = a.to_character_id";
$params = [];
if ($filterWork) { $sql .= ' WHERE a.work_id = ?'; $params[] = $filterWork; }
$sql .= ' ORDER BY w.title_kana, cf.name_kana, ct.name_kana';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

admin_header('呼称管理', 'appellations');
?>
<div class="toolbar">
  <form method="get" action="<?= base_url('admin/appellations.php') ?>">
    <select name="work_id" onchange="this.form.submit()"><?= select_options(['' => 'すべての作品'] + $workOptions, (string)($filterWork ?: '')) ?></select>
  </form>
  <?php if (has_role('curator')): ?>
  <a class="btn" href="<?= base_url('admin/appellations.php?action=new' . ($filterWork ? '&work_id=' . $filterWork : '')) ?>">＋ 呼称を登録</a>
  <?php endif; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <tr><th>作品</th><th>呼ぶ側</th><th>呼ばれる側</th><th>呼称</th><th>場面</th><th>出典</th><th>信頼度</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td class="muted"><?= h($r['work_title']) ?></td>
      <td><?= h($r['from_name']) ?></td>
      <td><?= h($r['to_name']) ?></td>
      <td><span class="appellation-cell"><?= h($r['appellation']) ?></span></td>
      <td><?= h($r['scene']) ?></td>
      <td class="muted"><?= h($r['source_note']) ?></td>
      <td><?= reliability_badge($r['reliability']) ?></td>
      <td style="white-space:nowrap">
        <?php if (can_edit_work($u, (int)$r['work_id'])): ?>
        <a class="btn btn-sub btn-sm" href="<?= base_url('admin/appellations.php?action=edit&id=' . $r['id']) ?>">編集</a>
        <form method="post" action="<?= base_url('admin/appellations.php?id=' . $r['id']) ?>" style="display:inline" onsubmit="return confirm('この呼称を削除しますか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">削除</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8" class="muted">呼称が登録されていません。</td></tr><?php endif; ?>
  </table>
</div>
<?php admin_footer(); ?>
