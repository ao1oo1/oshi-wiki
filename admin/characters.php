<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$filterWork = (int)($_GET['work_id'] ?? 0);

$works = $pdo->query('SELECT id, title FROM works ORDER BY title_kana, title')->fetchAll();
$workOptions = [];
foreach ($works as $w) $workOptions[$w['id']] = $w['title'];

$charFields = ['name','name_kana','alias','gender','age','birthday','height','weight','blood_type','affiliation','role','grade_class','species','first_appearance','personality','appearance','background','note'];
$speechFields = ['first_person','second_person','third_person','tone','endings','catchphrases','polite_speech','casual_targets','anger_tone','shy_tone','panic_tone','sad_tone','battle_tone','hero_attitude','writing_tips','source_note'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $post = $_POST['do'] ?? '';

    if ($post === 'save') {
        require_login('curator'); // キャラ作成・編集はCurator以上
        $workId = (int)($_POST['work_id'] ?? 0);
        require_work_editable($u, $workId);

        $data = ['work_id' => $workId];
        foreach ($charFields as $f) $data[$f] = trim((string)($_POST[$f] ?? ''));
        $data['status'] = array_key_exists($_POST['status'] ?? '', WORK_STATUS) ? $_POST['status'] : 'draft';

        if ($data['name'] === '' || !$workId) {
            flash('作品とキャラクター名は必須です。', 'error');
        } else {
            if ($id) {
                $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
                $pdo->prepare("UPDATE characters SET $set, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([...array_values($data), $id]);
                $charId = $id;
                audit('update', 'character', $charId, 'キャラ「' . $data['name'] . '」を更新');
            } else {
                $cols = implode(',', array_keys($data));
                $ph = implode(',', array_fill(0, count($data), '?'));
                $pdo->prepare("INSERT INTO characters ($cols) VALUES ($ph)")->execute(array_values($data));
                $charId = (int)$pdo->lastInsertId();
                audit('create', 'character', $charId, 'キャラ「' . $data['name'] . '」を登録');
            }

            // 口調プロファイル（同時保存）
            $sp = [];
            foreach ($speechFields as $f) $sp[$f] = trim((string)($_POST['sp_' . $f] ?? ''));
            $sp['reliability'] = array_key_exists($_POST['sp_reliability'] ?? '', RELIABILITY) ? $_POST['sp_reliability'] : 'unverified';
            $hasSpeech = implode('', $sp) !== '' && implode('', array_slice($sp, 0, -1)) !== '';

            $st = $pdo->prepare('SELECT id FROM speech_profiles WHERE character_id = ? ORDER BY id DESC LIMIT 1');
            $st->execute([$charId]);
            $spId = $st->fetchColumn();
            if ($spId) {
                $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($sp)));
                $pdo->prepare("UPDATE speech_profiles SET $set, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([...array_values($sp), $spId]);
            } elseif ($hasSpeech) {
                $sp['character_id'] = $charId;
                $cols = implode(',', array_keys($sp));
                $ph = implode(',', array_fill(0, count($sp), '?'));
                $pdo->prepare("INSERT INTO speech_profiles ($cols) VALUES ($ph)")->execute(array_values($sp));
            }

            flash($id ? 'キャラクターを更新しました。' : 'キャラクターを登録しました。');
            redirect('admin/characters.php?work_id=' . $workId);
        }
    }

    if ($post === 'delete') {
        require_login('admin'); // キャラ削除はAdmin以上
        $st = $pdo->prepare('SELECT name, work_id FROM characters WHERE id = ?');
        $st->execute([$id]);
        $c = $st->fetch();
        if ($c) {
            $pdo->prepare('DELETE FROM speech_profiles WHERE character_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM appellations WHERE from_character_id = ? OR to_character_id = ?')->execute([$id, $id]);
            $pdo->prepare('DELETE FROM relationships WHERE from_character_id = ? OR to_character_id = ?')->execute([$id, $id]);
            $pdo->prepare('DELETE FROM characters WHERE id = ?')->execute([$id]);
            audit('delete', 'character', $id, 'キャラ「' . $c['name'] . '」を削除');
            flash('キャラクターを削除しました。');
        }
        redirect('admin/characters.php');
    }
}

if ($action === 'edit' || $action === 'new') {
    require_login('curator');
    $char = array_fill_keys($charFields, '');
    $char['work_id'] = $filterWork ?: ($works[0]['id'] ?? 0);
    $char['status'] = 'draft';
    $sp = array_fill_keys($speechFields, '');
    $sp['reliability'] = 'unverified';

    if ($id) {
        $st = $pdo->prepare('SELECT * FROM characters WHERE id = ?');
        $st->execute([$id]);
        $char = $st->fetch() ?: $char;
        $st = $pdo->prepare('SELECT * FROM speech_profiles WHERE character_id = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$id]);
        $sp = $st->fetch() ?: $sp;
    }
    admin_header($id ? 'キャラクターを編集' : 'キャラクターを登録', 'characters');
    ?>
    <div class="form-card" style="max-width:900px">
      <form method="post" action="<?= base_url('admin/characters.php?action=edit' . ($id ? '&id=' . $id : '')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">

        <fieldset class="group"><legend>基本情報</legend>
        <div class="form-grid">
          <label class="field">作品 <span class="req">必須</span><select name="work_id"><?= select_options($workOptions, (string)$char['work_id']) ?></select></label>
          <label class="field">編集ステータス<select name="status"><?= select_options(WORK_STATUS, $char['status']) ?></select></label>
          <label class="field">名前 <span class="req">必須</span><input type="text" name="name" value="<?= h($char['name']) ?>" required></label>
          <label class="field">読み仮名<input type="text" name="name_kana" value="<?= h($char['name_kana']) ?>"></label>
          <label class="field">別名・通称<input type="text" name="alias" value="<?= h($char['alias']) ?>"></label>
          <label class="field">性別<input type="text" name="gender" value="<?= h($char['gender']) ?>"></label>
          <label class="field">年齢<input type="text" name="age" value="<?= h($char['age']) ?>"></label>
          <label class="field">誕生日<input type="text" name="birthday" value="<?= h($char['birthday']) ?>"></label>
          <label class="field">身長<input type="text" name="height" value="<?= h($char['height']) ?>"></label>
          <label class="field">体重<input type="text" name="weight" value="<?= h($char['weight']) ?>"></label>
          <label class="field">血液型<input type="text" name="blood_type" value="<?= h($char['blood_type']) ?>"></label>
          <label class="field">所属<input type="text" name="affiliation" value="<?= h($char['affiliation']) ?>"></label>
          <label class="field">役職<input type="text" name="role" value="<?= h($char['role']) ?>"></label>
          <label class="field">学年・クラス<input type="text" name="grade_class" value="<?= h($char['grade_class']) ?>"></label>
          <label class="field">種族・属性<input type="text" name="species" value="<?= h($char['species']) ?>"></label>
          <label class="field">初登場<input type="text" name="first_appearance" value="<?= h($char['first_appearance']) ?>"></label>
          <label class="field full">性格<textarea name="personality"><?= h($char['personality']) ?></textarea></label>
          <label class="field full">外見の特徴<textarea name="appearance"><?= h($char['appearance']) ?></textarea></label>
          <label class="field full">背景・経歴<textarea name="background"><?= h($char['background']) ?></textarea></label>
          <label class="field full">創作時の注意点<textarea name="note"><?= h($char['note']) ?></textarea></label>
        </div>
        </fieldset>

        <fieldset class="group"><legend>口調・一人称</legend>
        <div class="form-grid">
          <label class="field">一人称<input type="text" name="sp_first_person" value="<?= h($sp['first_person']) ?>"></label>
          <label class="field">二人称<input type="text" name="sp_second_person" value="<?= h($sp['second_person']) ?>"></label>
          <label class="field">三人称<input type="text" name="sp_third_person" value="<?= h($sp['third_person']) ?>"></label>
          <label class="field">語尾<input type="text" name="sp_endings" value="<?= h($sp['endings']) ?>"></label>
          <label class="field full">基本口調<textarea name="sp_tone"><?= h($sp['tone']) ?></textarea></label>
          <label class="field full">口癖<textarea name="sp_catchphrases"><?= h($sp['catchphrases']) ?></textarea></label>
          <label class="field">敬語を使う相手<input type="text" name="sp_polite_speech" value="<?= h($sp['polite_speech']) ?>"></label>
          <label class="field">呼び捨てにする相手<input type="text" name="sp_casual_targets" value="<?= h($sp['casual_targets']) ?>"></label>
          <label class="field">怒った時<input type="text" name="sp_anger_tone" value="<?= h($sp['anger_tone']) ?>"></label>
          <label class="field">照れた時<input type="text" name="sp_shy_tone" value="<?= h($sp['shy_tone']) ?>"></label>
          <label class="field">焦った時<input type="text" name="sp_panic_tone" value="<?= h($sp['panic_tone']) ?>"></label>
          <label class="field">悲しい時<input type="text" name="sp_sad_tone" value="<?= h($sp['sad_tone']) ?>"></label>
          <label class="field">戦闘時<input type="text" name="sp_battle_tone" value="<?= h($sp['battle_tone']) ?>"></label>
          <label class="field">情報の信頼度<select name="sp_reliability"><?= select_options(RELIABILITY, $sp['reliability']) ?></select></label>
          <label class="field full">主人公・ヒロインへの接し方<textarea name="sp_hero_attitude"><?= h($sp['hero_attitude']) ?></textarea></label>
          <label class="field full">文章にする時の注意点<textarea name="sp_writing_tips"><?= h($sp['writing_tips']) ?></textarea></label>
          <label class="field full">出典メモ<input type="text" name="sp_source_note" value="<?= h($sp['source_note']) ?>" placeholder="例：原作2巻 / アニメ第3話"></label>
        </div>
        </fieldset>

        <div class="form-actions">
          <button class="btn" type="submit"><?= $id ? '更新する' : '登録する' ?></button>
          <a class="btn btn-sub" href="<?= base_url('admin/characters.php') ?>">一覧に戻る</a>
        </div>
      </form>
    </div>
    <?php
    admin_footer();
    exit;
}

$sql = "SELECT c.*, w.title AS work_title,
        (SELECT first_person FROM speech_profiles sp WHERE sp.character_id = c.id ORDER BY sp.id DESC LIMIT 1) AS first_person
        FROM characters c JOIN works w ON w.id = c.work_id";
$params = [];
if ($filterWork) { $sql .= ' WHERE c.work_id = ?'; $params[] = $filterWork; }
$sql .= ' ORDER BY w.title_kana, c.name_kana, c.name';
$st = $pdo->prepare($sql);
$st->execute($params);
$chars = $st->fetchAll();

admin_header('キャラクター管理', 'characters');
?>
<div class="toolbar">
  <form method="get" action="<?= base_url('admin/characters.php') ?>">
    <select name="work_id" onchange="this.form.submit()">
      <?= select_options(['' => 'すべての作品'] + $workOptions, (string)($filterWork ?: '')) ?>
    </select>
  </form>
  <?php if (has_role('curator')): ?>
  <a class="btn" href="<?= base_url('admin/characters.php?action=new' . ($filterWork ? '&work_id=' . $filterWork : '')) ?>">＋ キャラクターを登録</a>
  <?php endif; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <tr><th>名前</th><th>作品</th><th>所属</th><th>一人称</th><th>ステータス</th><th></th></tr>
    <?php foreach ($chars as $c): ?>
    <tr>
      <td><b><?= h($c['name']) ?></b><br><span class="muted"><?= h($c['name_kana']) ?></span></td>
      <td><?= h($c['work_title']) ?></td>
      <td><?= h($c['affiliation']) ?></td>
      <td><?= $c['first_person'] ? '<span class="chip">' . h($c['first_person']) . '</span>' : '<span class="muted">—</span>' ?></td>
      <td><?= status_badge($c['status']) ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-sub btn-sm" href="<?= base_url('character.php?id=' . $c['id']) ?>">表示</a>
        <?php if (can_edit_work($u, (int)$c['work_id'])): ?>
        <a class="btn btn-sub btn-sm" href="<?= base_url('admin/characters.php?action=edit&id=' . $c['id']) ?>">編集</a>
        <?php endif; ?>
        <?php if (has_role('admin')): ?>
        <form method="post" action="<?= base_url('admin/characters.php?id=' . $c['id']) ?>" style="display:inline" onsubmit="return confirm('キャラ「<?= h($c['name']) ?>」を口調・呼称・関係性ごと削除します。よろしいですか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">削除</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$chars): ?><tr><td colspan="6" class="muted">キャラクターが登録されていません。</td></tr><?php endif; ?>
  </table>
</div>
<?php admin_footer(); ?>
