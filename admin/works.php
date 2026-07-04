<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $post = $_POST['do'] ?? '';

    if ($post === 'save') {
        // 作成はAdmin以上、編集は担当Curator以上
        if ($id) { require_login('curator'); require_work_editable($u, $id); }
        else { require_login('admin'); }

        $fields = [
            'title' => trim((string)$_POST['title']),
            'title_kana' => trim((string)$_POST['title_kana']),
            'genre' => trim((string)$_POST['genre']),
            'medium' => trim((string)$_POST['medium']),
            'official_url' => trim((string)$_POST['official_url']),
            'guideline_url' => trim((string)$_POST['guideline_url']),
            'description' => trim((string)$_POST['description']),
            'caution' => trim((string)$_POST['caution']),
            'status' => array_key_exists($_POST['status'] ?? '', WORK_STATUS) ? $_POST['status'] : 'draft',
        ];
        if ($fields['title'] === '') {
            flash('作品名は必須です。', 'error');
        } elseif ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
            $pdo->prepare("UPDATE works SET $set, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([...array_values($fields), $id]);
            audit('update', 'work', $id, '作品「' . $fields['title'] . '」を更新');
            flash('作品を更新しました。');
            redirect('admin/works.php');
        } else {
            $cols = implode(',', array_keys($fields));
            $ph = implode(',', array_fill(0, count($fields), '?'));
            $pdo->prepare("INSERT INTO works ($cols) VALUES ($ph)")->execute(array_values($fields));
            audit('create', 'work', (int)$pdo->lastInsertId(), '作品「' . $fields['title'] . '」を登録');
            flash('作品を登録しました。');
            redirect('admin/works.php');
        }
    }

    if ($post === 'delete') {
        require_login('super_admin'); // 作品削除は最高管理者のみ
        $st = $pdo->prepare('SELECT title FROM works WHERE id = ?');
        $st->execute([$id]);
        $title = $st->fetchColumn();
        foreach (['appellations', 'relationships', 'terms', 'sources', 'characters'] as $t) {
            $pdo->prepare("DELETE FROM $t WHERE work_id = ?")->execute([$id]);
        }
        $pdo->prepare('DELETE FROM speech_profiles WHERE character_id NOT IN (SELECT id FROM characters)')->execute();
        $pdo->prepare('DELETE FROM works WHERE id = ?')->execute([$id]);
        audit('delete', 'work', $id, '作品「' . $title . '」を関連データごと削除');
        flash('作品を削除しました。');
        redirect('admin/works.php');
    }
}

if ($action === 'edit' || $action === 'new') {
    $work = ['title' => '', 'title_kana' => '', 'genre' => '', 'medium' => '', 'official_url' => '', 'guideline_url' => '', 'description' => '', 'caution' => '', 'status' => 'draft'];
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM works WHERE id = ?');
        $st->execute([$id]);
        $work = $st->fetch() ?: $work;
    }
    admin_header($id ? '作品を編集' : '作品を登録', 'works');
    ?>
    <div class="form-card">
      <form method="post" action="<?= base_url('admin/works.php?action=edit' . ($id ? '&id=' . $id : '')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <div class="form-grid">
          <label class="field">作品名 <span class="req">必須</span><input type="text" name="title" value="<?= h($work['title']) ?>" required></label>
          <label class="field">読み仮名<input type="text" name="title_kana" value="<?= h($work['title_kana']) ?>"></label>
          <label class="field">ジャンル<input type="text" name="genre" value="<?= h($work['genre']) ?>" placeholder="例：学園ファンタジー"></label>
          <label class="field">原作媒体<input type="text" name="medium" value="<?= h($work['medium']) ?>" placeholder="例：漫画 / アニメ / ゲーム"></label>
          <label class="field">公式サイトURL<input type="url" name="official_url" value="<?= h($work['official_url']) ?>"></label>
          <label class="field">公式ガイドラインURL<input type="url" name="guideline_url" value="<?= h($work['guideline_url']) ?>"></label>
          <label class="field full">作品概要<textarea name="description"><?= h($work['description']) ?></textarea></label>
          <label class="field full">注意事項<textarea name="caution"><?= h($work['caution']) ?></textarea></label>
          <label class="field">編集ステータス<select name="status"><?= select_options(WORK_STATUS, $work['status']) ?></select></label>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit"><?= $id ? '更新する' : '登録する' ?></button>
          <a class="btn btn-sub" href="<?= base_url('admin/works.php') ?>">一覧に戻る</a>
        </div>
      </form>
    </div>
    <?php
    admin_footer();
    exit;
}

$works = $pdo->query("SELECT w.*, (SELECT COUNT(*) FROM characters c WHERE c.work_id = w.id) AS char_count FROM works w ORDER BY w.updated_at DESC")->fetchAll();
admin_header('作品管理', 'works');
?>
<div class="toolbar">
  <span class="muted"><?= count($works) ?>件</span>
  <?php if (has_role('admin')): ?><a class="btn" href="<?= base_url('admin/works.php?action=new') ?>">＋ 作品を登録</a><?php endif; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <tr><th>作品名</th><th>ジャンル</th><th>キャラ数</th><th>ステータス</th><th>更新日</th><th></th></tr>
    <?php foreach ($works as $w): ?>
    <tr>
      <td><b><?= h($w['title']) ?></b><br><span class="muted"><?= h($w['title_kana']) ?></span></td>
      <td><?= h($w['genre']) ?></td>
      <td><?= (int)$w['char_count'] ?></td>
      <td><?= status_badge($w['status']) ?></td>
      <td class="muted"><?= h(substr((string)$w['updated_at'], 0, 10)) ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-sub btn-sm" href="<?= base_url('work.php?id=' . $w['id']) ?>">表示</a>
        <?php if (can_edit_work($u, (int)$w['id'])): ?>
          <a class="btn btn-sub btn-sm" href="<?= base_url('admin/works.php?action=edit&id=' . $w['id']) ?>">編集</a>
        <?php endif; ?>
        <?php if (has_role('super_admin')): ?>
        <form method="post" action="<?= base_url('admin/works.php?id=' . $w['id']) ?>" style="display:inline" onsubmit="return confirm('作品「<?= h($w['title']) ?>」をキャラ・呼称・用語・出典ごと削除します。よろしいですか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">削除</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php admin_footer(); ?>
