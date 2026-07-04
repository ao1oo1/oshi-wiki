<?php
require __DIR__ . '/../app/helpers.php';
if (current_user()) redirect('admin/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // 簡易ブルートフォース対策：5回失敗で60秒待機
    $fails = $_SESSION['login_fails'] ?? 0;
    $last = $_SESSION['login_last_fail'] ?? 0;
    if ($fails >= 5 && time() - $last < 60) {
        $error = 'ログイン試行が多すぎます。1分ほど待ってからお試しください。';
    } else {
        $st = db()->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active'");
        $st->execute([trim((string)($_POST['email'] ?? ''))]);
        $user = $st->fetch();
        if ($user && password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            unset($_SESSION['login_fails'], $_SESSION['login_last_fail']);
            db()->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$user['id']]);
            redirect('admin/index.php');
        }
        $_SESSION['login_fails'] = $fails + 1;
        $_SESSION['login_last_fail'] = time();
        $error = 'メールアドレスまたはパスワードが正しくありません。';
    }
}
$cfg = config();
?>
<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>管理ログイン | <?= h($cfg['site_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
</head><body>
<div class="login-wrap">
  <div class="login-card">
    <h1><span class="brand-mark">✦</span> <?= h($cfg['site_name']) ?></h1>
    <span class="muted">管理画面ログイン</span>
    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label class="field">メールアドレス
        <input type="email" name="email" required autofocus>
      </label>
      <label class="field" style="margin-top:12px">パスワード
        <input type="password" name="password" required>
      </label>
      <div class="form-actions">
        <button class="btn btn-block" type="submit" style="width:100%">ログイン</button>
      </div>
    </form>
    <p class="muted" style="text-align:center;margin-top:18px"><a href="<?= base_url('index.php') ?>">← サイトに戻る</a></p>
  </div>
</div>
</body></html>
