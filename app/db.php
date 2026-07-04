<?php
/** DB接続・スキーマ初期化・シード投入 */

function config(): array {
    static $config = null;
    if ($config === null) $config = require __DIR__ . '/config.php';
    return $config;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $cfg = config();
    if ($cfg['db_driver'] === 'mysql') {
        $m = $cfg['mysql'];
        $dsn = "mysql:host={$m['host']};dbname={$m['dbname']};charset={$m['charset']}";
        $pdo = new PDO($dsn, $m['user'], $m['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $path = $cfg['sqlite_path'];
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    init_schema($pdo);
    return $pdo;
}

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void {
    $isMysql = config()['db_driver'] === 'mysql';
    if ($isMysql) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $st->execute([$table, $column]);
        if ((int)$st->fetchColumn() === 0) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    } else {
        $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll();
        foreach ($cols as $c) if (($c['name'] ?? '') === $column) return;
        $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
    }
}

function init_schema(PDO $pdo): void {
    $isMysql = config()['db_driver'] === 'mysql';
    $pk = $isMysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $now = "DEFAULT CURRENT_TIMESTAMP";
    $suffix = $isMysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

    $tables = [
    "CREATE TABLE IF NOT EXISTS works (
        id $pk,
        title VARCHAR(255) NOT NULL,
        title_kana VARCHAR(255) DEFAULT '',
        genre VARCHAR(100) DEFAULT '',
        medium VARCHAR(100) DEFAULT '',
        official_url TEXT,
        guideline_url TEXT,
        description TEXT,
        caution TEXT,
        status VARCHAR(30) DEFAULT 'draft',
        created_at DATETIME $now,
        updated_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS characters (
        id $pk,
        work_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        name_kana VARCHAR(255) DEFAULT '',
        alias VARCHAR(255) DEFAULT '',
        gender VARCHAR(50) DEFAULT '',
        age VARCHAR(50) DEFAULT '',
        birthday VARCHAR(50) DEFAULT '',
        height VARCHAR(50) DEFAULT '',
        weight VARCHAR(50) DEFAULT '',
        blood_type VARCHAR(20) DEFAULT '',
        affiliation VARCHAR(255) DEFAULT '',
        role VARCHAR(255) DEFAULT '',
        grade_class VARCHAR(100) DEFAULT '',
        species VARCHAR(100) DEFAULT '',
        first_appearance VARCHAR(255) DEFAULT '',
        personality TEXT,
        appearance TEXT,
        background TEXT,
        note TEXT,
        status VARCHAR(30) DEFAULT 'draft',
        created_at DATETIME $now,
        updated_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS speech_profiles (
        id $pk,
        character_id INTEGER NOT NULL,
        first_person VARCHAR(100) DEFAULT '',
        second_person VARCHAR(100) DEFAULT '',
        third_person VARCHAR(100) DEFAULT '',
        tone TEXT,
        endings VARCHAR(255) DEFAULT '',
        catchphrases TEXT,
        polite_speech TEXT,
        casual_targets TEXT,
        anger_tone TEXT,
        shy_tone TEXT,
        panic_tone TEXT,
        sad_tone TEXT,
        battle_tone TEXT,
        hero_attitude TEXT,
        writing_tips TEXT,
        source_note VARCHAR(255) DEFAULT '',
        reliability VARCHAR(30) DEFAULT 'unverified',
        created_at DATETIME $now,
        updated_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS appellations (
        id $pk,
        work_id INTEGER NOT NULL,
        from_character_id INTEGER NOT NULL,
        to_character_id INTEGER NOT NULL,
        appellation VARCHAR(255) NOT NULL,
        scene VARCHAR(255) DEFAULT '',
        note TEXT,
        source_note VARCHAR(255) DEFAULT '',
        reliability VARCHAR(30) DEFAULT 'unverified',
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS relationships (
        id $pk,
        work_id INTEGER NOT NULL,
        from_character_id INTEGER NOT NULL,
        to_character_id INTEGER NOT NULL,
        relationship_type VARCHAR(100) DEFAULT '',
        description TEXT,
        source_note VARCHAR(255) DEFAULT '',
        reliability VARCHAR(30) DEFAULT 'unverified',
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS terms (
        id $pk,
        work_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        name_kana VARCHAR(255) DEFAULT '',
        type VARCHAR(50) DEFAULT 'other',
        description TEXT,
        source_note VARCHAR(255) DEFAULT '',
        reliability VARCHAR(30) DEFAULT 'unverified',
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS sources (
        id $pk,
        work_id INTEGER NOT NULL,
        source_type VARCHAR(50) DEFAULT 'other',
        title VARCHAR(255) DEFAULT '',
        volume VARCHAR(50) DEFAULT '',
        episode VARCHAR(100) DEFAULT '',
        page VARCHAR(50) DEFAULT '',
        url TEXT,
        checked_at VARCHAR(50) DEFAULT '',
        note TEXT,
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS admin_users (
        id $pk,
        email VARCHAR(255) NOT NULL UNIQUE,
        display_name VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(30) NOT NULL DEFAULT 'viewer',
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        last_login_at DATETIME,
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS curator_assignments (
        id $pk,
        user_id INTEGER NOT NULL,
        work_id INTEGER NOT NULL,
        assigned_by INTEGER,
        created_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS submissions (
        id $pk,
        kind VARCHAR(30) NOT NULL DEFAULT 'info',
        submitter_name VARCHAR(255) DEFAULT '',
        submitter_contact VARCHAR(255) DEFAULT '',
        work_id INTEGER,
        character_id INTEGER,
        content TEXT NOT NULL,
        source_note TEXT,
        info_type VARCHAR(30) NOT NULL DEFAULT 'sourced',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        review_note TEXT,
        reviewed_by INTEGER,
        created_at DATETIME $now,
        updated_at DATETIME $now
    )$suffix",

    "CREATE TABLE IF NOT EXISTS audit_logs (
        id $pk,
        user_id INTEGER,
        action VARCHAR(50) NOT NULL,
        target_type VARCHAR(50) NOT NULL,
        target_id INTEGER,
        summary TEXT,
        created_at DATETIME $now
    )$suffix",
    ];

    foreach ($tables as $sql) $pdo->exec($sql);

    // 既存DB向けの軽量マイグレーション
    ensure_column($pdo, 'submissions', 'info_type', "VARCHAR(30) NOT NULL DEFAULT 'sourced'");

    // 初期管理者
    $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($count === 0) {
        $a = config()['initial_admin'];
        $st = $pdo->prepare('INSERT INTO admin_users (email, display_name, password_hash, role) VALUES (?,?,?,?)');
        $st->execute([$a['email'], $a['name'], password_hash($a['password'], PASSWORD_DEFAULT), 'super_admin']);
    }

    // サンプルデータ
    if (config()['seed_sample_data']) {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM works')->fetchColumn();
        if ($count === 0) seed_sample_data($pdo);
    }
}

/** デモ用のオリジナル架空作品データ（著作権に配慮し実在作品は使わない） */
function seed_sample_data(PDO $pdo): void {
    $pdo->prepare('INSERT INTO works (title, title_kana, genre, medium, description, caution, status) VALUES (?,?,?,?,?,?,?)')
        ->execute([
            'サンプル作品：星降学園物語', 'ほしふりがくえんものがたり', '学園ファンタジー', 'オリジナル（デモ用）',
            'このサイトの使い方を確認するためのデモ用オリジナル作品です。星降学園を舞台に、星の力を扱う生徒たちの日常を描きます。実在の作品ではありません。',
            'デモデータです。実運用時は管理画面から削除できます。',
            'verified',
        ]);
    $workId = (int)$pdo->lastInsertId();

    $chars = [
        ['橘 湊', 'たちばな みなと', '', '男', '17', '7月7日', '178cm', '', 'O型', '星降学園 生徒会', '生徒会長', '2年A組',
         '面倒見がよく努力家。表向きは完璧な生徒会長だが、身内には少し抜けた一面を見せる。',
         '黒髪、切れ長の目。制服を着崩さない。', '旧家の長男。弟妹の世話をして育った。', 'verified'],
        ['白瀬 こより', 'しらせ こより', 'こよ', '女', '16', '2月14日', '155cm', '', 'A型', '星降学園 天文部', '部長', '1年C組',
         '明るく人懐っこいムードメーカー。落ち込むと分かりやすく星の話を始める。',
         '栗色のショートボブ。星形の髪留めがトレードマーク。', '離島出身。星が一番きれいな場所を探して転入してきた。', 'verified'],
        ['如月 玲', 'きさらぎ れい', '', '女', '17', '11月30日', '164cm', '', 'AB型', '星降学園 風紀委員', '風紀委員長', '2年B組',
         'クールで規律に厳しいが、根は情に厚い。甘いものに弱いことを隠している。',
         '長い黒髪のポニーテール。常に姿勢が良い。', '湊とは幼なじみ。昔は泣き虫だった。', 'in_progress'],
    ];
    $charIds = [];
    $st = $pdo->prepare('INSERT INTO characters (work_id,name,name_kana,alias,gender,age,birthday,height,weight,blood_type,affiliation,role,grade_class,personality,appearance,background,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($chars as $c) {
        $st->execute([$workId, $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9], $c[10], $c[11], $c[12], $c[13], $c[14], $c[15]]);
        $charIds[] = (int)$pdo->lastInsertId();
    }

    $speech = [
        [$charIds[0], '俺', '君 / お前（親しい相手）', '呼び捨てまたは「〜さん」',
         '落ち着いた口調。生徒会業務では丁寧語、私的な場面では砕けた話し方。',
         '〜だな / 〜だろう', '「まったく、世話が焼ける」',
         '目上・保護者・来客には敬語', '幼なじみの玲、後輩のこより',
         '声を荒げず、低く静かになる', '早口になり話題を変えようとする', '', '', '',
         'こよりには兄のように接する。からかいつつも常に気にかけている。',
         '一人称「俺」を「僕」にしない。敬語とタメ口の使い分けが個性。', 'official'],
        [$charIds[1], 'こより / わたし', '〇〇くん / 〇〇ちゃん / 先輩',
         '基本「〜さん」だが親しくなると名前呼び',
         '明るく弾む口調。感嘆詞が多い。基本タメ口だが先輩には敬語混じり。',
         '〜だよ！ / 〜なの / 〜かも', '「星が呼んでる気がする！」',
         '先生と初対面の相手', '同級生',
         'むくれて頬をふくらませる。「もう！」を連発', '語尾が小さくなり早口になる', 'あわあわと擬音が増える', '', '',
         '湊を「会長」と呼び懐いている。玲には少し緊張しつつ憧れている。',
         '一人称は感情が高ぶると「こより」になる。ネガティブな言葉をほぼ使わない。', 'official'],
        [$charIds[2], '私', 'あなた / 貴様（怒った時）', '姓で呼び捨て',
         '端的で硬い口調。命令形が多いが、丁寧語は崩さない。',
         '〜です / 〜しなさい', '「規律です」',
         '基本的に全員に丁寧語', '幼なじみの湊のみ',
         '敬語のまま声が冷える。「貴様」が出たら本気', '無言になり髪を触る', '', '', '口数が減り指示のみ簡潔に出す',
         '湊にだけ素の口調（タメ口）が出る。こよりの自由さに振り回されがち。',
         '公私で口調が変わるのが最大の特徴。湊への呼称は「湊」、公の場では「橘会長」。', 'sourced'],
    ];
    $st = $pdo->prepare('INSERT INTO speech_profiles (character_id,first_person,second_person,third_person,tone,endings,catchphrases,polite_speech,casual_targets,anger_tone,shy_tone,panic_tone,sad_tone,battle_tone,hero_attitude,writing_tips,reliability) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($speech as $s) $st->execute($s);

    $apps = [
        [$charIds[0], $charIds[1], 'こより', '通常', '後輩として可愛がっている', '本編第2話', 'official'],
        [$charIds[0], $charIds[2], '玲', '通常', '幼なじみ', '本編第1話', 'official'],
        [$charIds[1], $charIds[0], '会長', '通常', '尊敬・懐き', '本編第2話', 'official'],
        [$charIds[1], $charIds[2], '玲先輩', '通常', '憧れ', '本編第3話', 'official'],
        [$charIds[2], $charIds[0], '湊', '私的な場面', '幼なじみ', '本編第4話', 'sourced'],
        [$charIds[2], $charIds[0], '橘会長', '公の場', '職務上', '本編第1話', 'official'],
        [$charIds[2], $charIds[1], '白瀬さん', '通常', '後輩', '本編第3話', 'official'],
    ];
    $st = $pdo->prepare('INSERT INTO appellations (work_id,from_character_id,to_character_id,appellation,scene,note,source_note,reliability) VALUES (?,?,?,?,?,?,?,?)');
    foreach ($apps as $a) $st->execute([$workId, $a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6]]);

    $rels = [
        [$charIds[0], $charIds[2], '幼なじみ', '家が隣同士で家族ぐるみの付き合い。互いに素を見せられる数少ない相手。', '本編第4話', 'official'],
        [$charIds[1], $charIds[0], '先輩後輩', '天文部の活動場所を巡って生徒会と交渉して以来の付き合い。', '本編第2話', 'official'],
    ];
    $st = $pdo->prepare('INSERT INTO relationships (work_id,from_character_id,to_character_id,relationship_type,description,source_note,reliability) VALUES (?,?,?,?,?,?,?)');
    foreach ($rels as $r) $st->execute([$workId, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]]);

    $terms = [
        ['星降学園', 'ほしふりがくえん', 'school', '物語の舞台となる全寮制の学園。星の力「ステラ」を扱う素質を持つ生徒が集まる。', '公式サイト', 'official'],
        ['ステラ', 'すてら', 'other', '星から降り注ぐ力の総称。生徒ごとに扱える系統が異なる。', '本編第1話', 'official'],
        ['天文部', 'てんもんぶ', 'organization', 'こよりが部長を務める部活動。部員は3名。', '本編第2話', 'official'],
    ];
    $st = $pdo->prepare('INSERT INTO terms (work_id,name,name_kana,type,description,source_note,reliability) VALUES (?,?,?,?,?,?,?)');
    foreach ($terms as $t) $st->execute([$workId, $t[0], $t[1], $t[2], $t[3], $t[4], $t[5]]);

    $sources = [
        [$workId, 'web', '公式サイト（デモ）', '', '', '', 'https://example.com', '2026-07-01', 'デモ用の架空URL'],
        [$workId, 'anime', '本編第1話「星の降る夜に」', '', '第1話', '', '', '2026-07-01', ''],
        [$workId, 'anime', '本編第2話「天文部へようこそ」', '', '第2話', '', '', '2026-07-01', ''],
    ];
    $st = $pdo->prepare('INSERT INTO sources (work_id,source_type,title,volume,episode,page,url,checked_at,note) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach ($sources as $s) $st->execute($s);
}
