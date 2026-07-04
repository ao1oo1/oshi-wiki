<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();

$works = $pdo->query('SELECT id, title FROM works ORDER BY title_kana, title')->fetchAll();
$workOptions = ['' => '（作品を選択）'];
foreach ($works as $w) $workOptions[$w['id']] = $w['title'];

$selWork = (int)($_GET['work_id'] ?? 0);
$selChar = (int)($_GET['character_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $content = trim((string)($_POST['content'] ?? ''));
    $kind = in_array($_POST['kind'] ?? '', ['info', 'fix'], true) ? $_POST['kind'] : 'info';
    if ($content === '') {
        flash('提供内容を入力してください。', 'error');
    } elseif (mb_strlen($content) > 5000) {
        flash('提供内容は5000文字以内で入力してください。', 'error');
    } else {
        $infoType = array_key_exists($_POST['info_type'] ?? '', INFO_TYPES) ? $_POST['info_type'] : 'sourced';
        $pdo->prepare('INSERT INTO submissions (kind, submitter_name, submitter_contact, work_id, character_id, content, source_note, info_type) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([
                $kind,
                mb_strimwidth(trim((string)($_POST['submitter_name'] ?? '')), 0, 200),
                mb_strimwidth(trim((string)($_POST['submitter_contact'] ?? '')), 0, 200),
                (int)($_POST['work_id'] ?? 0) ?: null,
                (int)($_POST['character_id'] ?? 0) ?: null,
                $content,
                mb_strimwidth(trim((string)($_POST['source_note'] ?? '')), 0, 1000),
                $infoType,
            ]);
        flash('送信しました。内容は担当者が確認したうえで反映されます。ご協力ありがとうございます。');
        redirect('submit.php');
    }
}

$chars = [];
if ($selWork) {
    $st = $pdo->prepare('SELECT id, name FROM characters WHERE work_id = ? ORDER BY name_kana');
    $st->execute([$selWork]);
    $chars = $st->fetchAll();
}
$charOptions = ['' => '（キャラクターを選択・任意）'];
foreach ($chars as $c) $charOptions[$c['id']] = $c['name'];

page_header('情報提供・修正依頼', 'submit');
?>
<h1>情報提供・修正依頼</h1>
<p>キャラクターの一人称・呼称・口調などの情報提供、掲載内容の修正依頼を受け付けています。送信内容はすぐには公開されず、担当者の確認・承認後に反映されます。</p>
<p class="muted">※ セリフ全文の書き起こしや公式資料の丸写しはご遠慮ください。ご自身の言葉での要約と、出典（巻数・話数など）を添えてください。</p>

<div class="form-card" style="margin-top:24px">
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label class="field">種別
        <select name="kind">
          <option value="info">情報提供（新しい情報の追加）</option>
          <option value="fix">修正依頼（掲載内容の誤り）</option>
        </select>
      </label>
      <label class="field">対象の作品
        <select name="work_id" onchange="location.href='<?= base_url('submit.php') ?>?work_id='+this.value">
          <?= select_options($workOptions, (string)($selWork ?: '')) ?>
        </select>
      </label>
      <label class="field full">対象のキャラクター（任意）
        <select name="character_id"><?= select_options($charOptions, (string)($selChar ?: '')) ?></select>
      </label>
      <label class="field full">情報区分
        <select name="info_type"><?= select_options(INFO_TYPES, 'sourced') ?></select>
      </label>
      <label class="field full">提供内容 <span class="req">必須</span>
        <textarea name="content" required placeholder="例：〇〇の一人称は「俺」ではなく「僕」です。第3話で確認できます。考察の場合は、公式情報と分けて記載してください。"></textarea>
      </label>
      <label class="field full">出典（巻数・話数・URLなど）
        <input type="text" name="source_note" placeholder="例：原作2巻 / アニメ第3話 / 公式サイト">
      </label>
      <label class="field">お名前・ハンドルネーム（任意）
        <input type="text" name="submitter_name">
      </label>
      <label class="field">連絡先（任意・確認が必要な場合のみ使用）
        <input type="text" name="submitter_contact" placeholder="メールアドレスやSNSのID">
      </label>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">送信する</button>
      <span class="muted">送信内容は承認されるまで公開されません</span>
    </div>
  </form>
</div>
<?php page_footer(); ?>
