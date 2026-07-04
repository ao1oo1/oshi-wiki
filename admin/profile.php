<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('viewer');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $st = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
    $st->execute([$u['id']]);
    $fresh = $st->fetch();
    if (!$fresh || !password_verify($current, $fresh['password_hash'])) {
        flash('現在のパスワードが正しくありません。', 'error');
    } elseif (strlen($new) < 8) {
        flash('新しいパスワードは8文字以上で入力してください。', 'error');
    } elseif ($new !== $confirm) {
        flash('新しいパスワードと確認用パスワードが一致しません。', 'error');
    } else {
        $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
        audit('password_change', 'admin_user', (int)$u['id'], '自分のパスワードを変更');
        flash('パスワードを変更しました。');
        redirect('admin/profile.php');
    }
}

admin_header('アカウント設定', 'profile');
?>
<div class="card" style="margin-bottom:20px">
  <h2 class="section-title">ログイン中のアカウント</h2>
  <dl class="dl">
    <dt>表示名</dt><dd><?= h($u['display_name']) ?></dd>
    <dt>メール</dt><dd><?= h($u['email']) ?></dd>
    <dt>権限</dt><dd><?= h(ROLE_LABELS[$u['role']] ?? $u['role']) ?></dd>
    <dt>最終ログイン</dt><dd><?= h($u['last_login_at'] ?? '—') ?></dd>
  </dl>
</div>

<div class="form-card">
  <h2 class="section-title">パスワード変更</h2>
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-grid">
      <label class="field full">現在のパスワード <span class="req">必須</span><input type="password" name="current_password" required></label>
      <label class="field">新しいパスワード <span class="req">必須</span><input type="password" name="new_password" required minlength="8"></label>
      <label class="field">新しいパスワード（確認） <span class="req">必須</span><input type="password" name="confirm_password" required minlength="8"></label>
    </div>
    <div class="form-actions"><button class="btn" type="submit">変更する</button></div>
  </form>
</div>
<?php admin_footer(); ?>
