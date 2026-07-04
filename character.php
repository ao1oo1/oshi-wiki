<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$tab = $_GET['tab'] ?? 'basic';
$tabs = ['basic' => '基本情報', 'speech' => '口調・一人称', 'appellations' => '呼称一覧', 'relations' => '関係性', 'sources' => '出典'];
if (!isset($tabs[$tab])) $tab = 'basic';

$st = $pdo->prepare('SELECT c.*, w.title AS work_title FROM characters c JOIN works w ON w.id = c.work_id WHERE c.id = ?');
$st->execute([$id]);
$c = $st->fetch();
if (!$c) { http_response_code(404); page_header('キャラクターが見つかりません'); echo '<div class="card"><p>お探しのキャラクターは見つかりませんでした。</p></div>'; page_footer(); exit; }

$st = $pdo->prepare('SELECT * FROM speech_profiles WHERE character_id = ? ORDER BY id DESC LIMIT 1');
$st->execute([$id]);
$sp = $st->fetch();

$st = $pdo->prepare('SELECT name FROM characters WHERE work_id = ?');
$stAll = $pdo->prepare('SELECT id, name FROM characters WHERE work_id = ?');
$stAll->execute([$c['work_id']]);
$charNames = [];
foreach ($stAll->fetchAll() as $row) $charNames[$row['id']] = $row['name'];

$st = $pdo->prepare('SELECT * FROM appellations WHERE from_character_id = ? OR to_character_id = ? ORDER BY id');
$st->execute([$id, $id]);
$apps = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM relationships WHERE from_character_id = ? OR to_character_id = ? ORDER BY id');
$st->execute([$id, $id]);
$rels = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM sources WHERE work_id = ? ORDER BY id');
$st->execute([$c['work_id']]);
$sources = $st->fetchAll();

function tab_url(int $id, string $tab): string { return base_url("character.php?id=$id&tab=$tab"); }

page_header($c['name'], 'works');
?>
<p class="muted"><a href="<?= base_url('works.php') ?>">作品一覧</a> / <a href="<?= base_url('work.php?id=' . $c['work_id']) ?>"><?= h($c['work_title']) ?></a> / <?= h($c['name']) ?></p>
<h1><?= h($c['name']) ?> <?= status_badge($c['status']) ?></h1>
<p class="muted"><?= h($c['name_kana']) ?><?= $c['alias'] ? ' · 通称：' . h($c['alias']) : '' ?></p>

<?php if ($sp): ?>
<p>
  <span class="chip">一人称：<?= h($sp['first_person'] ?: '未登録') ?></span>
  <span class="chip">二人称：<?= h($sp['second_person'] ?: '未登録') ?></span>
  <?php if ($sp['endings']): ?><span class="chip">語尾：<?= h($sp['endings']) ?></span><?php endif; ?>
</p>
<?php endif; ?>

<nav class="tabs">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="<?= tab_url($id, $key) ?>"<?= $key === $tab ? ' class="active"' : '' ?>><?= h($label) ?></a>
  <?php endforeach; ?>
</nav>

<div class="tab-panel">
<?php if ($tab === 'basic'): ?>
  <dl class="dl">
    <dt>名前</dt><dd><?= h($c['name']) ?>（<?= h($c['name_kana']) ?>）</dd>
    <dt>別名・通称</dt><dd><?= h($c['alias']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>性別</dt><dd><?= h($c['gender']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>年齢</dt><dd><?= h($c['age']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>誕生日</dt><dd><?= h($c['birthday']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>身長 / 体重</dt><dd><?= h(trim($c['height'] . ' / ' . $c['weight'], ' /')) ?: '<span class="muted">—</span>' ?></dd>
    <dt>血液型</dt><dd><?= h($c['blood_type']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>所属</dt><dd><?= h($c['affiliation']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>役職</dt><dd><?= h($c['role']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>学年・クラス</dt><dd><?= h($c['grade_class']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>種族・属性</dt><dd><?= h($c['species']) ?: '<span class="muted">—</span>' ?></dd>
    <dt>初登場</dt><dd><?= h($c['first_appearance']) ?: '<span class="muted">—</span>' ?></dd>
  </dl>
  <section class="section"><h2 class="section-title">性格</h2><div class="card"><?= nl2p($c['personality']) ?></div></section>
  <section class="section"><h2 class="section-title">外見の特徴</h2><div class="card"><?= nl2p($c['appearance']) ?></div></section>
  <section class="section"><h2 class="section-title">背景・経歴</h2><div class="card"><?= nl2p($c['background']) ?></div></section>
  <?php if (trim((string)$c['note']) !== ''): ?>
  <section class="section"><h2 class="section-title">創作時の注意点</h2><div class="card"><?= nl2p($c['note']) ?></div></section>
  <?php endif; ?>

<?php elseif ($tab === 'speech'): ?>
  <?php if (!$sp): ?>
    <div class="card"><p>口調情報はまだ登録されていません。<a href="<?= base_url('submit.php?character_id=' . $id) ?>">情報提供</a>をお待ちしています。</p></div>
  <?php else: ?>
    <p><?= reliability_badge($sp['reliability']) ?> <span class="muted"><?= h($sp['source_note']) ?></span></p>
    <dl class="dl">
      <dt>一人称</dt><dd><?= h($sp['first_person']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>二人称</dt><dd><?= h($sp['second_person']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>三人称</dt><dd><?= h($sp['third_person']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>語尾</dt><dd><?= h($sp['endings']) ?: '<span class="muted">—</span>' ?></dd>
      <dt>口癖</dt><dd><?= nl2br(h($sp['catchphrases'])) ?: '<span class="muted">—</span>' ?></dd>
      <dt>敬語を使う相手</dt><dd><?= nl2br(h($sp['polite_speech'])) ?: '<span class="muted">—</span>' ?></dd>
      <dt>呼び捨てにする相手</dt><dd><?= nl2br(h($sp['casual_targets'])) ?: '<span class="muted">—</span>' ?></dd>
    </dl>
    <section class="section"><h2 class="section-title">基本口調</h2><div class="card"><?= nl2p($sp['tone']) ?></div></section>
    <section class="section">
      <h2 class="section-title">感情別の話し方</h2>
      <dl class="dl">
        <dt>怒った時</dt><dd><?= nl2br(h($sp['anger_tone'])) ?: '<span class="muted">—</span>' ?></dd>
        <dt>照れた時</dt><dd><?= nl2br(h($sp['shy_tone'])) ?: '<span class="muted">—</span>' ?></dd>
        <dt>焦った時</dt><dd><?= nl2br(h($sp['panic_tone'])) ?: '<span class="muted">—</span>' ?></dd>
        <dt>悲しい時</dt><dd><?= nl2br(h($sp['sad_tone'])) ?: '<span class="muted">—</span>' ?></dd>
        <dt>戦闘時</dt><dd><?= nl2br(h($sp['battle_tone'])) ?: '<span class="muted">—</span>' ?></dd>
      </dl>
    </section>
    <?php if (trim((string)$sp['hero_attitude']) !== ''): ?>
    <section class="section"><h2 class="section-title">主人公・ヒロインへの接し方</h2><div class="card"><?= nl2p($sp['hero_attitude']) ?></div></section>
    <?php endif; ?>
    <?php if (trim((string)$sp['writing_tips']) !== ''): ?>
    <section class="section"><h2 class="section-title">文章にする時の注意点</h2><div class="card"><?= nl2p($sp['writing_tips']) ?></div></section>
    <?php endif; ?>
  <?php endif; ?>

<?php elseif ($tab === 'appellations'): ?>
  <?php if (!$apps): ?>
    <div class="card"><p>呼称情報はまだ登録されていません。</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <tr><th>呼ぶ側</th><th>呼ばれる側</th><th>呼称</th><th>場面</th><th>備考</th><th>出典</th><th>信頼度</th></tr>
        <?php foreach ($apps as $a): ?>
        <tr>
          <td><?= $a['from_character_id'] == $id ? '<b>' . h($c['name']) . '</b>' : '<a href="' . base_url('character.php?id=' . $a['from_character_id'] . '&tab=appellations') . '">' . h($charNames[$a['from_character_id']] ?? '?') . '</a>' ?></td>
          <td><?= $a['to_character_id'] == $id ? '<b>' . h($c['name']) . '</b>' : '<a href="' . base_url('character.php?id=' . $a['to_character_id'] . '&tab=appellations') . '">' . h($charNames[$a['to_character_id']] ?? '?') . '</a>' ?></td>
          <td><span class="appellation-cell"><?= h($a['appellation']) ?></span></td>
          <td><?= h($a['scene']) ?></td>
          <td class="muted"><?= h($a['note']) ?></td>
          <td class="muted"><?= h($a['source_note']) ?></td>
          <td><?= reliability_badge($a['reliability']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endif; ?>

<?php elseif ($tab === 'relations'): ?>
  <?php if (!$rels): ?>
    <div class="card"><p>関係性情報はまだ登録されていません。</p></div>
  <?php else: ?>
    <div class="card-grid">
      <?php foreach ($rels as $r):
        $otherId = $r['from_character_id'] == $id ? $r['to_character_id'] : $r['from_character_id']; ?>
      <div class="card">
        <h3><a href="<?= base_url('character.php?id=' . $otherId . '&tab=relations') ?>"><?= h($charNames[$otherId] ?? '?') ?></a></h3>
        <p><span class="badge"><?= h($r['relationship_type']) ?></span> <?= reliability_badge($r['reliability']) ?></p>
        <p><?= nl2br(h($r['description'])) ?></p>
        <p class="muted"><?= h($r['source_note']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php elseif ($tab === 'sources'): ?>
  <?php if (!$sources): ?>
    <div class="card"><p>出典情報はまだ登録されていません。</p></div>
  <?php else: ?>
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
  <?php endif; ?>
<?php endif; ?>
</div>

<div class="card">
  <p>このキャラクターの情報に追加・修正があれば、<a href="<?= base_url('submit.php?work_id=' . $c['work_id'] . '&character_id=' . $id) ?>"><b>情報提供フォーム</b></a>からお知らせください。</p>
</div>
<?php page_footer(); ?>
