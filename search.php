<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();
$q = trim((string)($_GET['q'] ?? ''));
$like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

$works = $chars = $speech = $apps = $terms = [];
if ($q !== '') {
    $st = $pdo->prepare("SELECT * FROM works WHERE title LIKE ? OR title_kana LIKE ? ORDER BY title LIMIT 30");
    $st->execute([$like, $like]); $works = $st->fetchAll();

    $st = $pdo->prepare("SELECT c.*, w.title AS work_title FROM characters c JOIN works w ON w.id = c.work_id
        WHERE c.name LIKE ? OR c.name_kana LIKE ? OR c.alias LIKE ? OR c.affiliation LIKE ? ORDER BY c.name LIMIT 50");
    $st->execute([$like, $like, $like, $like]); $chars = $st->fetchAll();

    $st = $pdo->prepare("SELECT sp.*, c.name, c.id AS cid, w.title AS work_title FROM speech_profiles sp
        JOIN characters c ON c.id = sp.character_id JOIN works w ON w.id = c.work_id
        WHERE sp.first_person LIKE ? OR sp.second_person LIKE ? OR sp.endings LIKE ? OR sp.tone LIKE ? LIMIT 50");
    $st->execute([$like, $like, $like, $like]); $speech = $st->fetchAll();

    $st = $pdo->prepare("SELECT a.*, cf.name AS from_name, ct.name AS to_name, w.title AS work_title FROM appellations a
        JOIN characters cf ON cf.id = a.from_character_id JOIN characters ct ON ct.id = a.to_character_id
        JOIN works w ON w.id = a.work_id WHERE a.appellation LIKE ? LIMIT 50");
    $st->execute([$like]); $apps = $st->fetchAll();

    $st = $pdo->prepare("SELECT t.*, w.title AS work_title FROM terms t JOIN works w ON w.id = t.work_id
        WHERE t.name LIKE ? OR t.name_kana LIKE ? OR t.description LIKE ? LIMIT 50");
    $st->execute([$like, $like, $like]); $terms = $st->fetchAll();
}
$total = count($works) + count($chars) + count($speech) + count($apps) + count($terms);

page_header('検索', 'search');
?>
<h1>検索</h1>
<form class="search-box" action="<?= base_url('search.php') ?>" method="get" style="margin:20px 0">
  <input type="text" name="q" value="<?= h($q) ?>" placeholder="キャラ名・一人称・呼称で検索" autofocus>
  <button type="submit">検索</button>
</form>

<?php if ($q === ''): ?>
  <p class="muted">キーワードを入力してください。作品名・キャラ名・一人称・呼称・語尾・用語を横断検索できます。</p>
<?php else: ?>
  <p class="muted">「<?= h($q) ?>」の検索結果：<?= $total ?>件</p>

  <?php if ($works): ?>
  <section class="section"><h2 class="section-title">作品（<?= count($works) ?>）</h2>
    <div class="card-grid"><?php foreach ($works as $w): ?>
      <a class="card" href="<?= base_url('work.php?id=' . $w['id']) ?>"><h3><?= h($w['title']) ?></h3><div class="kana"><?= h($w['title_kana']) ?></div></a>
    <?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <?php if ($chars): ?>
  <section class="section"><h2 class="section-title">キャラクター（<?= count($chars) ?>）</h2>
    <div class="card-grid"><?php foreach ($chars as $c): ?>
      <a class="card" href="<?= base_url('character.php?id=' . $c['id']) ?>"><h3><?= h($c['name']) ?></h3><div class="meta"><?= h($c['work_title']) ?></div></a>
    <?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <?php if ($speech): ?>
  <section class="section"><h2 class="section-title">口調・一人称（<?= count($speech) ?>）</h2>
    <div class="table-wrap"><table class="data">
      <tr><th>キャラクター</th><th>作品</th><th>一人称</th><th>二人称</th><th>語尾</th></tr>
      <?php foreach ($speech as $s): ?>
      <tr>
        <td><a href="<?= base_url('character.php?id=' . $s['cid'] . '&tab=speech') ?>"><b><?= h($s['name']) ?></b></a></td>
        <td class="muted"><?= h($s['work_title']) ?></td>
        <td><?= h($s['first_person']) ?></td><td><?= h($s['second_person']) ?></td><td><?= h($s['endings']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($apps): ?>
  <section class="section"><h2 class="section-title">呼称（<?= count($apps) ?>）</h2>
    <div class="table-wrap"><table class="data">
      <tr><th>呼ぶ側</th><th>呼ばれる側</th><th>呼称</th><th>作品</th></tr>
      <?php foreach ($apps as $a): ?>
      <tr>
        <td><a href="<?= base_url('character.php?id=' . $a['from_character_id'] . '&tab=appellations') ?>"><?= h($a['from_name']) ?></a></td>
        <td><a href="<?= base_url('character.php?id=' . $a['to_character_id'] . '&tab=appellations') ?>"><?= h($a['to_name']) ?></a></td>
        <td><span class="appellation-cell"><?= h($a['appellation']) ?></span></td>
        <td class="muted"><?= h($a['work_title']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($terms): ?>
  <section class="section"><h2 class="section-title">用語（<?= count($terms) ?>）</h2>
    <div class="table-wrap"><table class="data">
      <tr><th>用語</th><th>種別</th><th>説明</th><th>作品</th></tr>
      <?php foreach ($terms as $t): ?>
      <tr>
        <td><b><?= h($t['name']) ?></b></td>
        <td><span class="badge"><?= h(TERM_TYPES[$t['type']] ?? $t['type']) ?></span></td>
        <td><?= h(mb_strimwidth((string)$t['description'], 0, 120, '…')) ?></td>
        <td class="muted"><a href="<?= base_url('work.php?id=' . $t['work_id']) ?>"><?= h($t['work_title']) ?></a></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($total === 0): ?>
    <div class="card"><p>該当する情報は見つかりませんでした。別のキーワードで試すか、<a href="<?= base_url('submit.php') ?>">情報提供フォーム</a>から登録を依頼できます。</p></div>
  <?php endif; ?>
<?php endif; ?>
<?php page_footer(); ?>
