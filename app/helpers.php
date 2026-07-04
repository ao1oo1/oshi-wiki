<?php
/** 共通ヘルパー：認証・CSRF・ラベル・レイアウト */

require_once __DIR__ . '/db.php';

// mbstring が無い環境向けの簡易フォールバック（さくらのレンタルサーバには標準搭載）
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s): int { return strlen($s); }
    function mb_strimwidth(string $s, int $start, int $width, string $trim = ''): string {
        return strlen($s) > $width ? substr($s, $start, $width) . $trim : $s;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function base_url(string $path = ''): string {
    // /admin/ 以下からでも正しいルート相対パスを返す
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $root = str_contains($script, '/admin/') ? dirname(dirname($script)) : dirname($script);
    $root = rtrim(str_replace('\\', '/', $root), '/');
    return $root . '/' . ltrim($path, '/');
}

function redirect(string $path): never {
    header('Location: ' . base_url($path));
    exit;
}

/* ---------- フラッシュメッセージ ---------- */
function flash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}
function render_flashes(): string {
    if (empty($_SESSION['flash'])) return '';
    $out = '';
    foreach ($_SESSION['flash'] as $f) {
        $cls = $f['type'] === 'error' ? 'flash flash-error' : 'flash';
        $out .= '<div class="' . $cls . '">' . h($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void {
    if (($_POST['_csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) {
        http_response_code(400);
        exit('不正なリクエストです（CSRFトークン不一致）。フォームを開き直してください。');
    }
}

/* ---------- 認証・権限 ---------- */
const ROLE_LEVELS = ['viewer' => 1, 'reviewer' => 2, 'curator' => 3, 'admin' => 4, 'super_admin' => 5];
const ROLE_LABELS = [
    'viewer' => '閲覧スタッフ', 'reviewer' => '確認担当', 'curator' => 'キュレーター',
    'admin' => '管理者', 'super_admin' => '最高管理者',
];

function current_user(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    static $user = false;
    if ($user === false) {
        $st = db()->prepare("SELECT * FROM admin_users WHERE id = ? AND status = 'active'");
        $st->execute([$_SESSION['admin_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

function role_level(?array $user): int {
    return $user ? (ROLE_LEVELS[$user['role']] ?? 0) : 0;
}

function has_role(string $minRole): bool {
    return role_level(current_user()) >= (ROLE_LEVELS[$minRole] ?? 99);
}

function require_login(string $minRole = 'viewer'): array {
    $u = current_user();
    if (!$u) redirect('admin/login.php');
    if (role_level($u) < (ROLE_LEVELS[$minRole] ?? 99)) {
        http_response_code(403);
        page_header('権限がありません');
        echo '<div class="card"><p>この操作を行う権限がありません（必要権限：' . h(ROLE_LABELS[$minRole] ?? $minRole) . ' 以上）。</p>'
           . '<p><a class="btn btn-sub" href="' . base_url('admin/index.php') . '">ダッシュボードへ戻る</a></p></div>';
        page_footer();
        exit;
    }
    return $u;
}

/** キュレーターは担当作品のみ編集可。admin以上は全作品可 */
function can_edit_work(array $user, int $workId): bool {
    if (role_level($user) >= ROLE_LEVELS['admin']) return true;
    if ($user['role'] !== 'curator') return false;
    $st = db()->prepare('SELECT COUNT(*) FROM curator_assignments WHERE user_id = ? AND work_id = ?');
    $st->execute([$user['id'], $workId]);
    return (int)$st->fetchColumn() > 0;
}

function require_work_editable(array $user, int $workId): void {
    if (!can_edit_work($user, $workId)) {
        http_response_code(403);
        page_header('権限がありません');
        echo '<div class="card"><p>この作品の担当キュレーターではないため編集できません。</p>'
           . '<p><a class="btn btn-sub" href="' . base_url('admin/index.php') . '">ダッシュボードへ戻る</a></p></div>';
        page_footer();
        exit;
    }
}

function audit(string $action, string $targetType, ?int $targetId, string $summary): void {
    $u = current_user();
    db()->prepare('INSERT INTO audit_logs (user_id, action, target_type, target_id, summary) VALUES (?,?,?,?,?)')
        ->execute([$u['id'] ?? null, $action, $targetType, $targetId, $summary]);
}

/* ---------- ラベル・バッジ ---------- */
const WORK_STATUS = ['draft' => '未整備', 'in_progress' => '整備中', 'verified' => '確認済み', 'needs_fix' => '要修正'];
const RELIABILITY = ['official' => '公式確認済み', 'sourced' => '出典あり', 'unverified' => '要確認', 'theory' => '考察', 'fan_memo' => '創作用メモ'];
const SOURCE_TYPES = ['manga' => '原作漫画', 'anime' => 'アニメ', 'game' => 'ゲーム', 'novel' => '小説', 'web' => '公式サイト', 'guidebook' => '公式ガイドブック', 'sns' => '公式SNS', 'other' => 'その他'];
const TERM_TYPES = ['place' => '地名', 'organization' => '組織名', 'skill' => '技名', 'item' => 'アイテム', 'title' => '役職', 'species' => '種族', 'school' => '学校', 'rule' => 'ルール', 'other' => 'その他'];
const SUBMISSION_STATUS = ['pending' => '承認待ち', 'reviewed' => '確認済み', 'approved' => '反映済み', 'rejected' => '見送り'];
const INFO_TYPES = ['official' => '公式情報', 'sourced' => '出典付き要約', 'theory' => '考察', 'fan_memo' => '創作用メモ'];
const SUBMISSION_KIND = ['info' => '情報提供', 'fix' => '修正依頼', 'takedown' => '削除依頼'];

function status_badge(string $status): string {
    $label = WORK_STATUS[$status] ?? $status;
    return '<span class="badge badge-' . h($status) . '">' . h($label) . '</span>';
}
function reliability_badge(?string $r): string {
    if (!$r) return '';
    $label = RELIABILITY[$r] ?? $r;
    return '<span class="badge badge-rel badge-rel-' . h($r) . '">' . h($label) . '</span>';
}
function submission_badge(string $s): string {
    $label = SUBMISSION_STATUS[$s] ?? $s;
    return '<span class="badge badge-sub-' . h($s) . '">' . h($label) . '</span>';
}

function select_options(array $options, ?string $selected): string {
    $out = '';
    foreach ($options as $val => $label) {
        $sel = ((string)$val === (string)$selected) ? ' selected' : '';
        $out .= '<option value="' . h((string)$val) . '"' . $sel . '>' . h($label) . '</option>';
    }
    return $out;
}

function nl2p(?string $text): string {
    $text = trim((string)$text);
    if ($text === '') return '<span class="muted">未登録</span>';
    return nl2br(h($text));
}

/* ---------- レイアウト ---------- */
function page_header(string $title, string $active = ''): void {
    $cfg = config();
    $site = $cfg['site_name'];
    $u = current_user();
    $nav = [
        ['index.php', 'トップ', 'home'],
        ['works.php', '作品一覧', 'works'],
        ['search.php', '検索', 'search'],
        ['about.php', 'OshiBaseについて', 'about'],
        ['community.php', 'キュレーター募集', 'community'],
        ['submit.php', '情報提供', 'submit'],
        ['terms.php', 'ガイドライン', 'terms'],
    ];
    echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' | ' . h($site) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . base_url('assets/style.css') . '">';
    echo '</head><body>';
    echo '<header class="site-header"><div class="container header-inner">';
    echo '<a class="brand" href="' . base_url('index.php') . '"><span class="brand-mark">✦</span> ' . h($site) . '</a>';
    echo '<nav class="site-nav">';
    foreach ($nav as [$href, $label, $key]) {
        $cls = $key === $active ? ' class="active"' : '';
        echo '<a' . $cls . ' href="' . base_url($href) . '">' . h($label) . '</a>';
    }
    if ($u) {
        echo '<a class="nav-admin" href="' . base_url('admin/index.php') . '">管理画面</a>';
    } else {
        echo '<a class="nav-admin" href="' . base_url('admin/login.php') . '">管理ログイン</a>';
    }
    echo '</nav></div></header><main class="container">';
    echo render_flashes();
}

function page_footer(): void {
    $cfg = config();
    echo '</main><footer class="site-footer"><div class="container">';
    echo '<p class="footer-copy">' . h($cfg['site_name']) . ' — ' . h($cfg['site_tagline']) . '</p>';
    echo '<p class="footer-links"><a href="' . base_url('terms.php') . '">利用規約・ガイドライン</a> · '
       . '<a href="' . base_url('takedown.php') . '">削除依頼</a> · '
       . '<a href="' . base_url('submit.php') . '">情報提供</a></p>';
    echo '</div></footer></body></html>';
}

/* 管理画面レイアウト（左サイドバー） */
function admin_header(string $title, string $active = ''): void {
    $cfg = config();
    $u = current_user();
    echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' | 管理画面 | ' . h($cfg['site_name']) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . base_url('assets/style.css') . '">';
    echo '</head><body class="admin-body">';
    echo '<div class="admin-layout">';
    echo '<aside class="admin-sidebar">';
    echo '<a class="brand" href="' . base_url('index.php') . '"><span class="brand-mark">✦</span> ' . h($cfg['site_name']) . '</a>';
    echo '<p class="sidebar-user">' . h($u['display_name'] ?? '') . '<br><span class="muted">' . h(ROLE_LABELS[$u['role'] ?? ''] ?? '') . '</span></p>';
    $menu = [
        ['index.php', 'ダッシュボード', 'dashboard', 'viewer'],
        ['works.php', '作品管理', 'works', 'viewer'],
        ['characters.php', 'キャラクター管理', 'characters', 'viewer'],
        ['appellations.php', '呼称管理', 'appellations', 'viewer'],
        ['terms_admin.php', '用語管理', 'terms', 'viewer'],
        ['sources.php', '出典管理', 'sources', 'viewer'],
        ['submissions.php', 'フォーム受付', 'submissions', 'viewer'],
        ['logs.php', '編集履歴', 'logs', 'viewer'],
        ['profile.php', 'アカウント設定', 'profile', 'viewer'],
        ['users.php', 'ユーザー・権限', 'users', 'super_admin'],
    ];
    echo '<nav class="admin-nav">';
    foreach ($menu as [$href, $label, $key, $minRole]) {
        if (role_level($u) < ROLE_LEVELS[$minRole]) continue;
        $cls = $key === $active ? ' class="active"' : '';
        echo '<a' . $cls . ' href="' . base_url('admin/' . $href) . '">' . h($label) . '</a>';
    }
    echo '</nav>';
    echo '<a class="btn btn-sub btn-block" href="' . base_url('admin/logout.php') . '">ログアウト</a>';
    echo '</aside><div class="admin-main"><h1 class="admin-title">' . h($title) . '</h1>';
    echo render_flashes();
}

function admin_footer(): void {
    echo '</div></div></body></html>';
}
