<?php
require __DIR__ . '/app/helpers.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $content = trim((string)($_POST['content'] ?? ''));
    $contact = trim((string)($_POST['submitter_contact'] ?? ''));
    if ($content === '' || $contact === '') {
        flash('依頼内容と連絡先は必須です。', 'error');
    } else {
        $pdo->prepare('INSERT INTO submissions (kind, submitter_name, submitter_contact, content) VALUES (?,?,?,?)')
            ->execute(['takedown',
                mb_strimwidth(trim((string)($_POST['submitter_name'] ?? '')), 0, 200),
                mb_strimwidth($contact, 0, 200),
                mb_strimwidth($content, 0, 5000)]);
        flash('削除依頼を受け付けました。確認のうえ、ご入力いただいた連絡先へご返信いたします。');
        redirect('takedown.php');
    }
}
page_header('削除依頼', 'terms');
?>
<h1>削除依頼フォーム</h1>
<p>権利者の方からの掲載内容の削除依頼を受け付けています。対象のページURLと削除を希望される理由をご記入ください。確認のうえ速やかに対応いたします。</p>

<div class="form-card" style="margin-top:24px">
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label class="field full">依頼内容（対象ページのURL・理由） <span class="req">必須</span>
        <textarea name="content" required placeholder="対象ページのURLと、削除を希望される理由をご記入ください。"></textarea>
      </label>
      <label class="field">お名前・ご所属
        <input type="text" name="submitter_name">
      </label>
      <label class="field">連絡先 <span class="req">必須</span>
        <input type="text" name="submitter_contact" required placeholder="返信可能なメールアドレス">
      </label>
    </div>
    <div class="form-actions"><button class="btn" type="submit">送信する</button></div>
  </form>
</div>
<?php page_footer(); ?>
