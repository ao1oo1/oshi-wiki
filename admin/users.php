<?php
require __DIR__ . '/../app/helpers.php';
$u = require_login('super_admin'); // 権限管理は最高管理者のみ
$pdo = db();

$works = $pdo->query('SELECT id, title FROM works ORDER BY title_kana, title')->fetchAll();
$workOptions = [];
foreach ($works as $w) $workOptions[$w['id']] = $w['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';
    $targetId = (int)($_POST['id'] ?? 0);

    if ($do === 'create') {
        $email = trim((string)($_POST['email'] ?? ''));
        $name = trim((string)($_POST['display_name'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        $role = array_key_exists($_POST['role'] ?? '', ROLE_LEVELS) ? $_POST['role'] : 'viewer';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($pass) < 8) {
            flash('メールアドレス・表示名・パスワード（8文字以上）を正しく入力してください。', 'error');
        } else {
            try {
                $pdo->prepare('INSERT INTO admin_users (email, display_name, password_hash, role) VALUES (?,?,?,?)')
                    ->execute([$email, $name, password_hash($pass, PASSWORD_DEFAULT), $role]);
                audit('create', 'admin_user', (int)$pdo->lastInsertId(), '管理ユーザー「' . $name . '」（' . (ROLE_LABELS[$role] ?? $role) . '）を追加');
                flash('管理ユーザーを追加しました。');
            } catch (PDOException $e) {
                flash('このメールアドレスは既に登録されています。', 'error');
            }
        }
        redirect('admin/users.php');
    }

    if ($do === 'update_role') {
        $role = array_key_exists($_POST['role'] ?? '', ROLE_LEVELS) ? $_POST['role'] : 'viewer';
        if ($targetId === (int)$u['id']) {
            flash('自分自身の権限は変更できません。', 'error');
        } else {
            $pdo->prepare('UPDATE admin_users SET role = ? WHERE id = ?')->execute([$role, $targetId]);
            audit('update', 'admin_user', $targetId, '権限を「' . (ROLE_LABELS[$role] ?? $role) . '」に変更');
            flash('権限を変更しました。');
        }
        redirect('admin/users.php');
    }

    if ($do === 'toggle_status') {
        if ($targetId === (int)$u['id']) {
            flash('自分自身のアカウントは停止できません。', 'error');
        } else {
            $st = $pdo->prepare('SELECT status FROM admin_users WHERE id = ?');
            $st->execute([$targetId]);
            $cur = $st->fetchColumn();
            $new = $cur === 'active' ? 'suspended' : 'active';
            $pdo->prepare('UPDATE admin_users SET status = ? WHERE id = ?')->execute([$new, $targetId]);
            audit('update', 'admin_user', $targetId, 'アカウントを' . ($new === 'active' ? '再開' : '停止'));
            flash($new === 'active' ? 'アカウントを再開しました。' : 'アカウントを停止しました。');
        }
        redirect('admin/users.php');
    }

    if ($do === 'reset_password') {
        $pass = (string)($_POST['password'] ?? '');
        if (strlen($pass) < 8) {
            flash('パスワードは8文字以上にしてください。', 'error');
        } else {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $targetId]);
            audit('update', 'admin_user', $targetId, 'パスワードを再設定');
            flash('パスワードを再設定しました。');
        }
        redirect('admin/users.php');
    }

    if ($do === 'assign') {
        $workId = (int)($_POST['work_id'] ?? 0);
        if ($targetId && $workId) {
            $st = $pdo->prepare('SELECT COUNT(*) FROM curator_assignments WHERE user_id = ? AND work_id = ?');
            $st->execute([$targetId, $workId]);
            if (!(int)$st->fetchColumn()) {
                $pdo->prepare('INSERT INTO curator_assignments (user_id, work_id, assigned_by) VALUES (?,?,?)')
                    ->execute([$targetId, $workId, $u['id']]);
                audit('create', 'curator_assignment', $targetId, '担当作品を割り当て（work #' . $workId . '）');
                flash('担当作品を割り当てました。');
            }
        }
        redirect('admin/users.php');
    }

    if ($do === 'unassign') {
        $pdo->prepare('DELETE FROM curator_assignments WHERE id = ?')->execute([(int)($_POST['assignment_id'] ?? 0)]);
        flash('担当作品の割り当てを解除しました。');
        redirect('admin/users.php');
    }
}

$usersList = $pdo->query('SELECT * FROM admin_users ORDER BY id')->fetchAll();
$assignments = $pdo->query('SELECT ca.*, w.title FROM curator_assignments ca JOIN works w ON w.id = ca.work_id')->fetchAll();
$assignByUser = [];
foreach ($assignments as $a) $assignByUser[$a['user_id']][] = $a;

admin_header('ユーザー・権限管理', 'users');
?>
<h2 class="section-title">管理ユーザー一覧</h2>
<div class="table-wrap">
  <table class="data">
    <tr><th>表示名</th><th>メール</th><th>権限</th><th>担当作品</th><th>状態</th><th>最終ログイン</th><th></th></tr>
    <?php foreach ($usersList as $row): $isSelf = (int)$row['id'] === (int)$u['id']; ?>
    <tr>
      <td><b><?= h($row['display_name']) ?></b><?= $isSelf ? ' <span class="muted">(自分)</span>' : '' ?></td>
      <td class="muted"><?= h($row['email']) ?></td>
      <td>
        <?php if ($isSelf): ?>
          <span class="badge badge-verified"><?= h(ROLE_LABELS[$row['role']] ?? $row['role']) ?></span>
        <?php else: ?>
        <form method="post" style="display:flex;gap:6px;align-items:center">
          <?= csrf_field() ?><input type="hidden" name="do" value="update_role"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <select name="role" style="margin-top:0;width:auto"><?= select_options(ROLE_LABELS, $row['role']) ?></select>
          <button class="btn btn-sub btn-sm" type="submit">変更</button>
        </form>
        <?php endif; ?>
      </td>
      <td>
        <?php foreach ($assignByUser[$row['id']] ?? [] as $a): ?>
          <form method="post" style="display:inline">
            <?= csrf_field() ?><input type="hidden" name="do" value="unassign"><input type="hidden" name="assignment_id" value="<?= (int)$a['id'] ?>">
            <span class="chip"><?= h($a['title']) ?> <button type="submit" style="border:0;background:none;cursor:pointer;color:var(--sub)" title="解除">×</button></span>
          </form>
        <?php endforeach; ?>
        <form method="post" style="display:flex;gap:6px;margin-top:4px">
          <?= csrf_field() ?><input type="hidden" name="do" value="assign"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <select name="work_id" style="margin-top:0;width:auto;font-size:.8rem"><?= select_options(['' => '＋担当を追加'] + $workOptions, '') ?></select>
          <button class="btn btn-sub btn-sm" type="submit">追加</button>
        </form>
      </td>
      <td><?= $row['status'] === 'active' ? '<span class="badge badge-verified">有効</span>' : '<span class="badge badge-draft">停止中</span>' ?></td>
      <td class="muted"><?= h(substr((string)($row['last_login_at'] ?? ''), 0, 16)) ?: '—' ?></td>
      <td style="white-space:nowrap">
        <?php if (!$isSelf): ?>
        <form method="post" style="display:inline" onsubmit="return confirm('このアカウントの状態を切り替えますか？')">
          <?= csrf_field() ?><input type="hidden" name="do" value="toggle_status"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <button class="btn btn-danger btn-sm" type="submit"><?= $row['status'] === 'active' ? '停止' : '再開' ?></button>
        </form>
        <?php endif; ?>
        <details style="display:inline-block">
          <summary class="btn btn-sub btn-sm" style="list-style:none;cursor:pointer">PW再設定</summary>
          <form method="post" style="display:flex;gap:6px;margin-top:6px">
            <?= csrf_field() ?><input type="hidden" name="do" value="reset_password"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <input type="password" name="password" placeholder="新パスワード（8文字以上）" style="margin-top:0" required minlength="8">
            <button class="btn btn-sm" type="submit">設定</button>
          </form>
        </details>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<h2 class="section-title" style="margin-top:36px">管理ユーザーを追加</h2>
<div class="form-card">
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="do" value="create">
    <div class="form-grid">
      <label class="field">表示名 <span class="req">必須</span><input type="text" name="display_name" required></label>
      <label class="field">メールアドレス <span class="req">必須</span><input type="email" name="email" required></label>
      <label class="field">初期パスワード <span class="req">必須</span><input type="password" name="password" required minlength="8" placeholder="8文字以上"></label>
      <label class="field">権限<select name="role"><?= select_options(ROLE_LABELS, 'curator') ?></select></label>
    </div>
    <div class="form-actions"><button class="btn" type="submit">追加する</button></div>
  </form>
</div>

<div class="card" style="margin-top:24px">
  <p style="margin:0"><b>権限の目安：</b>閲覧スタッフ＝管理画面の閲覧のみ / 確認担当＝フォーム受付の確認 / キュレーター＝担当作品の編集と承認 / 管理者＝全作品の編集・承認・キャラ削除 / 最高管理者＝作品削除・権限管理を含む全操作。</p>
</div>
<?php admin_footer(); ?>
