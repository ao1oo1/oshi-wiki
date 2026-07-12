import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


/* STEP5A_ADMIN_MOBILE_UI_START */
(function () {
    const OLD_MENU_TEXTS = [
        'ダッシュボード',
        '承認待ち',
        'スタッフ申請',
        '作品管理',
        'キャラクター管理',
        '関係性管理',
        'タグ管理',
        'お問い合わせ受信箱',
        '公開ページ',
        'プロフィール設定'
    ];

    const INDEX_CONFIGS = {
        '/admin/works': {
            bodyClass: 'oshi-u-works-index',
            createHref: '/admin/works/create',
            createLabel: '作品登録画面へ',
            createFormText: '作品を新規登録',
            fields: [
                { label: '選択', patterns: ['選択'], fallback: 0, role: 'select' },
                { label: '名称', patterns: ['名称', '作品名', 'タイトル'], fallback: 1 },
                { label: '原作媒体', patterns: ['原作媒体', '媒体'], fallback: 3 },
                { label: 'ジャンル', patterns: ['ジャンル'], fallback: 2 },
                { label: 'タグ', patterns: ['タグ'], fallback: 4 }
            ]
        },
        '/admin/characters': {
            bodyClass: 'oshi-u-characters-index',
            createHref: '/admin/characters/create',
            createLabel: 'キャラクター登録画面へ',
            createFormText: 'キャラクターを新規登録',
            fields: [
                { label: '選択', patterns: ['選択'], fallback: 0, role: 'select' },
                { label: 'キャラクター名', patterns: ['キャラクター名', '名前', '名称'], fallback: 1 },
                { label: '作品', patterns: ['作品'], fallback: 2 }
            ]
        },
        '/admin/character-relationships': {
            bodyClass: 'oshi-u-relationships-index',
            createHref: '/admin/character-relationships/create',
            createLabel: '関係性登録画面へ',
            createFormText: '関係性を新規登録',
            fields: [
                { label: '選択', patterns: ['選択'], fallback: 0, role: 'select' },
                { label: 'キャラクター→相手キャラクター', patterns: ['関係性', 'キャラクター', '相手'], fallback: 1 },
                { label: '作品', patterns: ['作品'], fallback: 2 }
            ]
        },
        '/admin/tags': {
            bodyClass: 'oshi-u-tags-index',
            createHref: '/admin/tags?show_create=1',
            createLabel: 'タグ登録画面へ',
            createFormText: 'タグを新規登録',
            fields: [
                { label: '選択', patterns: ['選択'], fallback: 0, role: 'select' },
                { label: 'タグ名', patterns: ['タグ名', '名称', '名前'], fallback: 1 },
                { label: '種類', patterns: ['種類', '種別'], fallback: 2 },
                { label: '説明', patterns: ['説明', '概要'], fallback: 3 }
            ]
        }
    };

    const CREATE_CONFIGS = {
        '/admin/works/create': {
            bodyClass: 'oshi-u-work-create',
            panelTitle: '作品新規登録',
            backLinks: [
                { href: '/admin/works', label: '作品一覧へ戻る' }
            ],
            removeTexts: ['一覧へ戻る']
        },
        '/admin/characters/create': {
            bodyClass: 'oshi-u-character-create',
            panelTitle: 'キャラクター新規登録',
            backLinks: [
                { href: '/admin/characters', label: 'キャラクター一覧へ戻る' }
            ],
            removeTexts: ['キャラクター一覧へ']
        },
        '/admin/character-relationships/create': {
            bodyClass: 'oshi-u-relationship-create',
            panelTitle: '関係性新規登録',
            backLinks: [
                { href: '/admin/character-relationships', label: '関係性一覧へ' },
                { href: '/admin/characters', label: 'キャラクター管理へ' }
            ],
            removeTexts: []
        }
    };

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function mainEl() {
        return document.querySelector('.oshi-admin-main') || document.querySelector('main');
    }

    function cloneCell(cell) {
        const wrap = document.createElement('div');

        if (!cell) {
            wrap.textContent = '未設定';
            return wrap;
        }

        Array.from(cell.childNodes).forEach((node) => {
            wrap.appendChild(node.cloneNode(true));
        });

        if (!textOf(wrap) && !wrap.querySelector('input, select, textarea, button, a, form')) {
            wrap.textContent = '未設定';
        }

        return wrap;
    }

    function indexByHeader(headers, patterns, fallback) {
        const found = headers.findIndex((header) => {
            return patterns.some((pattern) => header.toLowerCase().includes(pattern.toLowerCase()));
        });

        return found >= 0 ? found : fallback;
    }

    function makeRow(label, content, role) {
        const row = document.createElement('div');
        row.className = 'oshi-u-card-row';

        if (role) {
            row.classList.add('is-' + role);
        }

        const labelEl = document.createElement('div');
        labelEl.className = 'oshi-u-card-label';
        labelEl.textContent = label;

        const valueEl = document.createElement('div');
        valueEl.className = 'oshi-u-card-value';

        if (content instanceof HTMLElement) {
            valueEl.appendChild(content);
        } else {
            valueEl.textContent = content || '未設定';
        }

        row.appendChild(labelEl);
        row.appendChild(valueEl);

        return row;
    }

    function makeStatusPill(text) {
        const pill = document.createElement('span');
        const value = text || '未設定';

        pill.className = 'oshi-u-status-pill';
        pill.textContent = value;

        if (value.includes('公開')) {
            pill.classList.add('is-public');
        } else if (value.includes('非公開')) {
            pill.classList.add('is-private');
        } else if (value.includes('下書き')) {
            pill.classList.add('is-draft');
        } else {
            pill.classList.add('is-unknown');
        }

        return pill;
    }

    function findStatusCell(cells) {
        const words = ['公開', '非公開', '下書き', 'published', 'private', 'draft'];

        for (let i = 0; i < cells.length; i++) {
            const text = textOf(cells[i]);

            if (words.some((word) => text.includes(word))) {
                return { index: i, text };
            }
        }

        return { index: -1, text: '未設定' };
    }

    function cellHasAction(cell) {
        if (!cell) return false;

        if (cell.querySelector('a, button, form')) {
            return true;
        }

        const text = textOf(cell);
        return ['詳細', '編集', '削除', '公開', '非公開', '下書き'].some((word) => text.includes(word));
    }

    function makeActions(cells, usedIndexes) {
        const actions = document.createElement('div');
        actions.className = 'oshi-u-card-actions';

        cells.forEach((cell, index) => {
            if (usedIndexes.includes(index)) return;
            if (!cellHasAction(cell)) return;

            const item = document.createElement('div');
            item.className = 'oshi-u-card-action-item';
            item.appendChild(cloneCell(cell));
            actions.appendChild(item);
        });

        if (!actions.children.length) {
            actions.textContent = '未設定';
        }

        return actions;
    }

    function hideOldMenuLinks(main) {
        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);

            if (OLD_MENU_TEXTS.includes(text)) {
                link.classList.add('oshi-u-old-menu-link-hidden');
            }
        });

        Array.from(main.querySelectorAll('div, nav, section')).forEach((el) => {
            if (el.querySelector('form, input, select, textarea, button')) return;

            const links = Array.from(el.querySelectorAll('a'));
            if (!links.length) return;

            const visibleLinks = links.filter((link) => !link.classList.contains('oshi-u-old-menu-link-hidden'));

            if (visibleLinks.length === 0) {
                el.classList.add('oshi-u-old-menu-block-hidden');
            }
        });
    }

    function hideUpperTitle(main) {
        const firstTitle = main.querySelector('h1, .oshi-admin-title');

        if (firstTitle) {
            firstTitle.classList.add('oshi-u-upper-title-hidden');
        }
    }

    function ensureCreateButton(main, config) {
        let buttonWrap = main.querySelector('.oshi-u-mobile-create-link');

        if (!buttonWrap) {
            buttonWrap = document.createElement('div');
            buttonWrap.className = 'oshi-u-mobile-create-link';

            const link = document.createElement('a');
            link.href = config.createHref;
            link.className = 'oshi-u-mobile-create-button';
            link.textContent = config.createLabel;

            buttonWrap.appendChild(link);
        }

        const importLink = Array.from(main.querySelectorAll('a, button')).find((el) => {
            const text = textOf(el);
            return text.includes('テキスト取り込み') || text.includes('CSV取り込み');
        });

        if (importLink) {
            const importArea =
                importLink.closest('.flex') ||
                importLink.closest('.inline-flex') ||
                importLink.parentElement;

            if (importArea && importArea.parentElement && !importArea.parentElement.contains(buttonWrap)) {
                importArea.parentElement.insertBefore(buttonWrap, importArea);
            }
        }
    }

    function hideIndexCreateForm(main, config) {
        Array.from(main.querySelectorAll('form')).forEach((form) => {
            const text = textOf(form);

            if (text.includes(config.createFormText)) {
                form.classList.add('oshi-u-index-create-form-hidden');
            }
        });
    }

    function buildIndexCards(main, config) {
        const table = main.querySelector('.oshi-table');

        if (!table || table.dataset.oshiUnifiedCardsReady === '1') {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const headers = Array.from(table.querySelectorAll('thead th')).map(textOf);

        if (!rows.length || !headers.length) {
            return;
        }

        const source = table.closest('.oshi-table-wrap') || table;
        source.classList.add('oshi-u-table-source');

        const list = document.createElement('div');
        list.className = 'oshi-u-card-list';

        rows.forEach((tr) => {
            const cells = Array.from(tr.children);
            if (!cells.length) return;

            const card = document.createElement('article');
            card.className = 'oshi-u-card';

            const used = [];

            config.fields.forEach((field) => {
                const index = indexByHeader(headers, field.patterns, field.fallback);
                used.push(index);
                card.appendChild(makeRow(field.label, cloneCell(cells[index]), field.role || ''));
            });

            const status = findStatusCell(cells);

            if (status.index >= 0) {
                used.push(status.index);
            }

            card.appendChild(makeRow('状態', makeStatusPill(status.text), 'status'));
            card.appendChild(makeRow('操作', makeActions(cells, used), 'actions'));

            list.appendChild(card);
        });

        source.insertAdjacentElement('beforebegin', list);
        table.dataset.oshiUnifiedCardsReady = '1';
    }

    function applyIndexPage() {
        const config = INDEX_CONFIGS[pathKey()];
        if (!config) return;

        const main = mainEl();
        if (!main) return;

        document.body.classList.add('oshi-u-mobile-index-page', config.bodyClass);

        hideOldMenuLinks(main);
        hideUpperTitle(main);
        ensureCreateButton(main, config);
        hideIndexCreateForm(main, config);
        buildIndexCards(main, config);
    }

    function addCreatePanelTitle(main, title) {
        const form = main.querySelector('form[action]');
        if (!form) return;

        const card =
            form.closest('.oshi-card') ||
            form.closest('.oshi-form-card') ||
            form.closest('.rounded') ||
            form.parentElement;

        if (!card) return;

        card.classList.add('oshi-u-create-card');
        form.classList.add('oshi-u-create-form');

        if (!card.querySelector('.oshi-u-create-panel-title')) {
            const heading = document.createElement('h2');
            heading.className = 'oshi-u-create-panel-title';
            heading.textContent = title;
            card.prepend(heading);
        }
    }

    function moveBackLinksToBottom(main, config) {
        let bottom = main.querySelector('.oshi-u-create-bottom-actions');

        if (!bottom) {
            bottom = document.createElement('div');
            bottom.className = 'oshi-u-create-bottom-actions';
            main.appendChild(bottom);
        }

        config.backLinks.forEach((item) => {
            let link = Array.from(main.querySelectorAll('a')).find((a) => textOf(a) === item.label);

            if (!link) {
                link = document.createElement('a');
                link.href = item.href;
                link.textContent = item.label;
            }

            link.classList.add('oshi-u-create-bottom-link');

            if (!bottom.contains(link)) {
                bottom.appendChild(link);
            }
        });

        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);

            if (bottom.contains(link)) return;

            if ((config.removeTexts || []).includes(text)) {
                link.classList.add('oshi-u-create-link-hidden');
                return;
            }

            if (text.includes('へ戻る') || text.includes('一覧へ')) {
                link.classList.add('oshi-u-create-link-hidden');
            }
        });
    }

    function applyRelationshipWorkSelect(main) {
        if (pathKey() !== '/admin/character-relationships/create') return;

        Array.from(main.querySelectorAll('h2, h3, h4')).forEach((heading) => {
            if (!textOf(heading).includes('作品を選択')) return;

            const card = heading.closest('div, section, article');
            if (card) {
                card.classList.add('oshi-u-work-select-card');
            }

            const form = card?.querySelector('form') || heading.parentElement?.querySelector('form');
            if (form) {
                form.classList.add('oshi-u-work-select-form');
            }
        });
    }

    function applyCreatePage() {
        const config = CREATE_CONFIGS[pathKey()];
        if (!config) return;

        const main = mainEl();
        if (!main) return;

        document.body.classList.add('oshi-u-mobile-create-page', config.bodyClass);

        hideOldMenuLinks(main);
        addCreatePanelTitle(main, config.panelTitle);
        moveBackLinksToBottom(main, config);
        applyRelationshipWorkSelect(main);
    }

    function apply() {
        if (!isMobile()) return;

        applyIndexPage();
        applyCreatePage();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
})();
/* STEP5A_ADMIN_MOBILE_UI_END */


/* STEP5F_ADMIN_MOBILE_LIST_CARD_FIX_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function normalizeAdminListPage() {
        if (!isMobile()) {
            return;
        }

        const path = pathKey();
        const targetPaths = [
            '/admin/works',
            '/admin/characters',
            '/admin/character-relationships',
            '/admin/tags'
        ];

        if (!targetPaths.includes(path)) {
            return;
        }

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) {
            return;
        }

        document.body.classList.add('oshi-u-list-card-polished');

        /*
         * 共通JSが作ったスマホ登録ボタン以外の、既存PC用登録ボタンをスマホでは非表示にする。
         */
        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);
            const href = link.getAttribute('href') || '';

            const isCreateLink =
                text.includes('登録画面へ') ||
                text === '新規登録' ||
                href.endsWith('/create') ||
                href.includes('/create?');

            if (isCreateLink && !link.classList.contains('oshi-u-mobile-create-button')) {
                link.classList.add('oshi-u-existing-create-link-hidden');
            }
        });

        /*
         * 念のため、スマホカード内のチェックボックスを小さい正方形に統一。
         */
        Array.from(main.querySelectorAll('.oshi-u-card input[type="checkbox"]')).forEach((checkbox) => {
            checkbox.classList.add('oshi-u-card-checkbox');
        });

        /*
         * 状態・操作の行だけ横幅いっぱいになりやすいようにclassを補強。
         */
        Array.from(main.querySelectorAll('.oshi-u-card-row')).forEach((row) => {
            const label = textOf(row.querySelector('.oshi-u-card-label'));

            if (label === '状態') {
                row.classList.add('is-status');
            }

            if (label === '操作') {
                row.classList.add('is-actions');
            }

            if (label === '選択') {
                row.classList.add('is-select');
            }
        });

        /*
         * 空カード・空操作の崩れ防止。
         */
        Array.from(main.querySelectorAll('.oshi-u-card-actions')).forEach((actions) => {
            if (!textOf(actions) && actions.children.length === 0) {
                actions.textContent = '未設定';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', normalizeAdminListPage);
    } else {
        normalizeAdminListPage();
    }
})();
/* STEP5F_ADMIN_MOBILE_LIST_CARD_FIX_END */


/* STEP5G_ADMIN_MOBILE_CREATE_UI_FIX_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    const CREATE_PAGES = {
        '/admin/works/create': {
            title: '作品新規登録',
            backLinks: [
                { href: '/admin/works', label: '作品一覧へ戻る' }
            ],
            hideTexts: ['一覧へ戻る', '作品一覧へ戻る']
        },
        '/admin/characters/create': {
            title: 'キャラクター新規登録',
            backLinks: [
                { href: '/admin/characters', label: 'キャラクター一覧へ戻る' }
            ],
            hideTexts: ['キャラクター一覧へ', 'キャラクター一覧へ戻る']
        },
        '/admin/character-relationships/create': {
            title: '関係性新規登録',
            backLinks: [
                { href: '/admin/character-relationships', label: '関係性一覧へ' },
                { href: '/admin/characters', label: 'キャラクター管理へ' }
            ],
            hideTexts: ['関係性一覧へ', 'キャラクター管理へ']
        }
    };

    function applyCreateUi() {
        if (!isMobile()) {
            return;
        }

        const config = CREATE_PAGES[pathKey()];
        if (!config) {
            return;
        }

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) {
            return;
        }

        document.body.classList.add('oshi-u-create-ui-polished');

        const forms = Array.from(main.querySelectorAll('form[action]'));
        const storeForm = forms.find((form) => {
            const method = (form.getAttribute('method') || '').toLowerCase();
            const action = form.getAttribute('action') || '';
            return method === 'post' && !action.includes('/bulk-action');
        }) || forms[0];

        if (storeForm) {
            storeForm.classList.add('oshi-u-polished-create-form');

            const card =
                storeForm.closest('.oshi-card') ||
                storeForm.closest('.oshi-form-card') ||
                storeForm.closest('.rounded') ||
                storeForm.parentElement;

            if (card) {
                card.classList.add('oshi-u-polished-create-card');

                let title = card.querySelector('.oshi-u-polished-create-title');

                if (!title) {
                    title = document.createElement('h2');
                    title.className = 'oshi-u-polished-create-title';
                    title.textContent = config.title;
                    card.prepend(title);
                }
            }

            Array.from(storeForm.querySelectorAll('button[type="submit"], input[type="submit"]')).forEach((button) => {
                button.classList.add('oshi-u-polished-submit');
            });
        }

        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);

            if (config.hideTexts.includes(text) || text.includes('一覧へ戻る')) {
                link.classList.add('oshi-u-create-top-link-hidden');
            }
        });

        let bottom = main.querySelector('.oshi-u-polished-bottom-actions');

        if (!bottom) {
            bottom = document.createElement('div');
            bottom.className = 'oshi-u-polished-bottom-actions';
            main.appendChild(bottom);
        }

        bottom.innerHTML = '';

        config.backLinks.forEach((item) => {
            const link = document.createElement('a');
            link.href = item.href;
            link.className = 'oshi-u-polished-back-link';
            link.textContent = item.label;
            bottom.appendChild(link);
        });

        /*
         * 関係性登録の「作品を選択」フォームは別カードとして整える。
         */
        if (pathKey() === '/admin/character-relationships/create') {
            Array.from(main.querySelectorAll('h2, h3, h4')).forEach((heading) => {
                if (!textOf(heading).includes('作品を選択')) {
                    return;
                }

                const card = heading.closest('div, section, article');
                if (card) {
                    card.classList.add('oshi-u-polished-work-select-card');
                }

                const form = card?.querySelector('form') || heading.parentElement?.querySelector('form');
                if (form) {
                    form.classList.add('oshi-u-polished-work-select-form');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyCreateUi);
    } else {
        applyCreateUi();
    }
})();
/* STEP5G_ADMIN_MOBILE_CREATE_UI_FIX_END */


/* STEP5I_WORK_CREATE_FORM_RESTORE_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function restoreWorkCreateForm() {
        if (!isMobile()) return;
        if (pathKey() !== '/admin/works/create') return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-work-create-form-restored');

        const form = main.querySelector('form[action*="/admin/works"]') || main.querySelector('form[action]');
        if (!form) return;

        form.classList.add('oshi-u-force-visible-create-form');

        const card =
            form.closest('.oshi-card') ||
            form.closest('.oshi-form-card') ||
            form.closest('.rounded') ||
            form.parentElement;

        if (card) {
            card.classList.add('oshi-u-force-visible-create-card');

            if (!card.querySelector('.oshi-u-work-create-fixed-title')) {
                const title = document.createElement('h2');
                title.className = 'oshi-u-work-create-fixed-title';
                title.textContent = '作品新規登録';
                card.prepend(title);
            }
        }

        /*
         * 上部の「作品登録」「一覧へ戻る」エリアだけを安全に非表示。
         * formを含む親要素は絶対に消さない。
         */
        Array.from(main.children).forEach((child) => {
            if (child.querySelector('form, input, select, textarea, button')) return;

            const text = (child.textContent || '').replace(/\s+/g, ' ').trim();

            if (text.includes('作品登録') || text.includes('一覧へ戻る')) {
                child.classList.add('oshi-u-safe-top-heading-hidden');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreWorkCreateForm);
    } else {
        restoreWorkCreateForm();
    }
})();
/* STEP5I_WORK_CREATE_FORM_RESTORE_END */


/* STEP5J_CREATE_DUPLICATE_TITLE_FIX_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function fixDuplicateCreateTitles() {
        if (!isMobile()) return;

        const targetPaths = [
            '/admin/works/create',
            '/admin/characters/create',
            '/admin/character-relationships/create'
        ];

        if (!targetPaths.includes(pathKey())) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        /*
         * STEP5Aで追加された旧タイトルを非表示。
         * STEP5Gの .oshi-u-polished-create-title だけを使う。
         */
        Array.from(main.querySelectorAll('.oshi-u-create-panel-title')).forEach((title) => {
            title.classList.add('oshi-u-duplicate-create-title-hidden');
        });

        /*
         * 万が一、同じテキストのタイトルが複数ある場合は最初の1つだけ残す。
         */
        const titles = Array.from(main.querySelectorAll('.oshi-u-polished-create-title, .oshi-u-work-create-fixed-title'));
        const seen = new Set();

        titles.forEach((title) => {
            const text = (title.textContent || '').replace(/\s+/g, ' ').trim();

            if (seen.has(text)) {
                title.classList.add('oshi-u-duplicate-create-title-hidden');
            } else {
                seen.add(text);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixDuplicateCreateTitles);
    } else {
        fixDuplicateCreateTitles();
    }
})();
/* STEP5J_CREATE_DUPLICATE_TITLE_FIX_END */


/* STEP5K_TAG_CREATE_SHOW_FIX_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function hasShowCreate() {
        return new URLSearchParams(window.location.search).get('show_create') === '1';
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function fixTagCreateDisplay() {
        if (!isMobile()) return;
        if (pathKey() !== '/admin/tags') return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        const createForm = Array.from(main.querySelectorAll('form')).find((form) => {
            return textOf(form).includes('タグを新規登録');
        });

        if (!createForm) return;

        if (hasShowCreate()) {
            document.body.classList.add('oshi-u-tag-create-open');

            createForm.classList.remove('oshi-u-index-create-form-hidden');
            createForm.classList.add('oshi-u-tag-create-form-visible');

            const section = createForm.querySelector('.oshi-tag-index-create-section');
            if (section) {
                section.classList.add('is-mobile-create-open');
            }

            /*
             * 登録フォーム表示時は、タグ登録画面へボタンは重複になるので隠す。
             */
            Array.from(main.querySelectorAll('a')).forEach((link) => {
                if (textOf(link).includes('タグ登録画面へ')) {
                    link.classList.add('oshi-u-tag-create-link-hidden');
                }
            });

            /*
             * フォームが見える位置へ寄せる。
             */
            setTimeout(function () {
                createForm.scrollIntoView({ block: 'start', behavior: 'smooth' });
            }, 80);
        } else {
            document.body.classList.add('oshi-u-tag-create-closed');

            createForm.classList.add('oshi-u-index-create-form-hidden');
            createForm.classList.remove('oshi-u-tag-create-form-visible');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixTagCreateDisplay);
    } else {
        fixTagCreateDisplay();
    }
})();
/* STEP5K_TAG_CREATE_SHOW_FIX_END */


/* STEP5L_CREATE_PAGE_SAFE_RENDER_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    const CONFIG = {
        '/admin/works/create': {
            title: '作品新規登録',
            formActionIncludes: '/admin/works',
            bottomLinks: [
                { href: '/admin/works', label: '作品一覧へ戻る' }
            ]
        },
        '/admin/characters/create': {
            title: 'キャラクター新規登録',
            formActionIncludes: '/admin/characters',
            bottomLinks: [
                { href: '/admin/characters', label: 'キャラクター一覧へ戻る' }
            ]
        },
        '/admin/character-relationships/create': {
            title: '関係性新規登録',
            formActionIncludes: '/admin/character-relationships',
            bottomLinks: [
                { href: '/admin/character-relationships', label: '関係性一覧へ' },
                { href: '/admin/characters', label: 'キャラクター管理へ' }
            ]
        }
    };

    const OLD_NAV_WORDS = [
        'ダッシュボード',
        '承認待ち',
        'スタッフ申請',
        '作品管理',
        'キャラクター管理',
        '関係性管理',
        'タグ管理',
        'お問い合わせ受信箱',
        '公開ページ',
        'プロフィール設定'
    ];

    function applySafeCreateRender() {
        if (!isMobile()) return;

        const config = CONFIG[pathKey()];
        if (!config) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-create-safe-render');

        const forms = Array.from(main.querySelectorAll('form[action]'));

        const storeForm = forms.find((form) => {
            const method = (form.getAttribute('method') || '').toLowerCase();
            const action = form.getAttribute('action') || '';

            return method === 'post' && action.includes(config.formActionIncludes) && !action.includes('bulk-action');
        });

        if (!storeForm) return;

        storeForm.classList.add('oshi-u-safe-create-form');

        const formCard =
            storeForm.closest('.oshi-card') ||
            storeForm.closest('.oshi-form-card') ||
            storeForm.closest('.rounded') ||
            storeForm.parentElement;

        if (!formCard) return;

        formCard.classList.add('oshi-u-safe-create-card');

        /*
         * 本文内ナビや上部戻るなど、フォームカードより前にある余計なブロックを隠す。
         * formを含む要素は絶対に非表示にしない。
         */
        Array.from(main.querySelectorAll('div, nav, section, header')).forEach((el) => {
            if (el === formCard || el.contains(formCard) || formCard.contains(el)) return;
            if (el.querySelector('form, input, select, textarea')) return;

            const text = textOf(el);
            const hasOldNav = OLD_NAV_WORDS.some((word) => text.includes(word));
            const hasBack = text.includes('一覧へ') || text.includes('戻る');
            const hasOldTitle = text.includes('作品登録') || text.includes('キャラクター登録') || text.includes('関係性登録');

            if (hasOldNav || hasBack || hasOldTitle) {
                el.classList.add('oshi-u-safe-create-hidden-block');
            }
        });

        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);
            const href = link.getAttribute('href') || '';

            const isTopBack =
                text.includes('一覧へ') ||
                text.includes('戻る') ||
                text.includes('キャラクター管理へ');

            const isOldNav = OLD_NAV_WORDS.includes(text);

            if (isTopBack || isOldNav || href === '/admin/dashboard') {
                link.classList.add('oshi-u-safe-create-hidden-link');
            }
        });

        /*
         * タイトルは1つだけ。
         */
        Array.from(formCard.querySelectorAll('.oshi-u-create-panel-title, .oshi-u-polished-create-title, .oshi-u-work-create-fixed-title')).forEach((title) => {
            title.remove();
        });

        const title = document.createElement('h2');
        title.className = 'oshi-u-safe-create-title';
        title.textContent = config.title;
        formCard.prepend(title);

        /*
         * フォームカードをmainの先頭寄りに移動。
         * これで、謎の空白やナビに押し下げられるのを防ぐ。
         */
        const firstUseful = Array.from(main.children).find((child) => {
            return !child.classList.contains('oshi-u-safe-create-hidden-block');
        });

        if (firstUseful && firstUseful !== formCard && main.contains(formCard)) {
            main.insertBefore(formCard, firstUseful);
        } else if (main.firstElementChild !== formCard) {
            main.insertBefore(formCard, main.firstElementChild);
        }

        /*
         * 送信ボタンを統一。
         */
        Array.from(storeForm.querySelectorAll('button[type="submit"], input[type="submit"]')).forEach((button) => {
            button.classList.add('oshi-u-safe-create-submit');
        });

        /*
         * 最下部戻るボタンを作り直す。
         */
        Array.from(main.querySelectorAll('.oshi-u-create-bottom-actions, .oshi-u-polished-bottom-actions')).forEach((el) => {
            el.remove();
        });

        const bottom = document.createElement('div');
        bottom.className = 'oshi-u-safe-create-bottom-actions';

        config.bottomLinks.forEach((item) => {
            const link = document.createElement('a');
            link.href = item.href;
            link.className = 'oshi-u-safe-create-bottom-link';
            link.textContent = item.label;
            bottom.appendChild(link);
        });

        main.appendChild(bottom);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applySafeCreateRender);
    } else {
        applySafeCreateRender();
    }
})();
/* STEP5L_CREATE_PAGE_SAFE_RENDER_END */


/* STEP5M_RELATIONSHIP_CREATE_BUTTON_FIX_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function fixRelationshipCreateButton() {
        if (!isMobile()) return;
        if (pathKey() !== '/admin/character-relationships') return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-relationship-create-button-fixed');

        /*
         * 既存のPC用「関係性登録画面へ」はスマホでは非表示にされているため、
         * スマホ専用ボタンを検索フォームの上に確実に作る。
         */
        let buttonWrap = main.querySelector('.oshi-u-relationship-mobile-create-link');

        if (!buttonWrap) {
            buttonWrap = document.createElement('div');
            buttonWrap.className = 'oshi-u-relationship-mobile-create-link';

            const link = document.createElement('a');
            link.href = '/admin/character-relationships/create';
            link.className = 'oshi-u-relationship-mobile-create-button';
            link.textContent = '関係性登録画面へ';

            buttonWrap.appendChild(link);
        }

        const searchForm = Array.from(main.querySelectorAll('form')).find((form) => {
            const method = (form.getAttribute('method') || '').toLowerCase();
            const action = form.getAttribute('action') || '';
            const text = textOf(form);

            return method === 'get' && action.includes('/admin/character-relationships') && text.includes('作品で絞り込み');
        });

        if (searchForm && searchForm.parentElement) {
            searchForm.parentElement.insertBefore(buttonWrap, searchForm);
        } else {
            const firstCard = main.querySelector('.oshi-card') || main.firstElementChild;
            if (firstCard) {
                firstCard.prepend(buttonWrap);
            } else {
                main.prepend(buttonWrap);
            }
        }

        /*
         * PC用の既存登録リンクはスマホでは隠す。
         * ただし、作成したスマホ専用ボタンは隠さない。
         */
        Array.from(main.querySelectorAll('a')).forEach((link) => {
            if (link.classList.contains('oshi-u-relationship-mobile-create-button')) return;

            const text = textOf(link);
            const href = link.getAttribute('href') || '';

            if (text.includes('関係性登録画面へ') || href.endsWith('/admin/character-relationships/create')) {
                link.classList.add('oshi-u-existing-create-link-hidden');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixRelationshipCreateButton);
    } else {
        fixRelationshipCreateButton();
    }
})();
/* STEP5M_RELATIONSHIP_CREATE_BUTTON_FIX_END */


/* STEP5O_CHARACTER_EDIT_TAG_UI_FIX_START */
(function () {
    function isCharacterEditPage() {
        return /^\/admin\/characters\/[^/]+\/edit\/?$/.test(window.location.pathname);
    }

    function fixCharacterEditTagUi() {
        if (!isCharacterEditPage()) return;

        document.body.classList.add('oshi-u-character-edit-tag-ui-fixed');

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        const tagArea = main.querySelector('.oshi-character-create-tags');
        if (!tagArea) return;

        tagArea.classList.add('oshi-character-edit-tags');

        Array.from(tagArea.querySelectorAll('label')).forEach((label) => {
            label.classList.add('oshi-character-edit-tag-option');
        });

        Array.from(tagArea.querySelectorAll('input[type="checkbox"]')).forEach((checkbox) => {
            checkbox.classList.add('oshi-character-edit-tag-checkbox');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixCharacterEditTagUi);
    } else {
        fixCharacterEditTagUi();
    }
})();
/* STEP5O_CHARACTER_EDIT_TAG_UI_FIX_END */


/* STEP5P_EDIT_TAG_UI_COMMON_FIX_START */
(function () {
    function isTargetEditPage() {
        return /^\/admin\/works\/[^/]+\/edit\/?$/.test(window.location.pathname) ||
               /^\/admin\/characters\/[^/]+\/edit\/?$/.test(window.location.pathname);
    }

    function fixEditTagUiCommon() {
        if (!isTargetEditPage()) return;

        document.body.classList.add('oshi-u-edit-tag-ui-fixed');

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        const tagAreas = Array.from(main.querySelectorAll(
            '.oshi-work-create-tags, .oshi-character-create-tags'
        ));

        tagAreas.forEach((tagArea) => {
            tagArea.classList.add('oshi-u-edit-tags');

            Array.from(tagArea.querySelectorAll('label')).forEach((label) => {
                label.classList.add('oshi-u-edit-tag-option');
            });

            Array.from(tagArea.querySelectorAll('input[type="checkbox"]')).forEach((checkbox) => {
                checkbox.classList.add('oshi-u-edit-tag-checkbox');
            });
        });

        /*
         * classが付いていない旧構造にも対応。
         * 「タグ」見出しの直後にあるチェックボックス群を検出する。
         */
        Array.from(main.querySelectorAll('label, p, div')).forEach((el) => {
            const text = (el.textContent || '').replace(/\s+/g, ' ').trim();
            if (text !== 'タグ') return;

            const parent = el.parentElement;
            if (!parent) return;

            const area = parent.querySelector('div:has(input[type="checkbox"])');
            if (!area) return;

            area.classList.add('oshi-u-edit-tags');

            Array.from(area.querySelectorAll('label')).forEach((label) => {
                label.classList.add('oshi-u-edit-tag-option');
            });

            Array.from(area.querySelectorAll('input[type="checkbox"]')).forEach((checkbox) => {
                checkbox.classList.add('oshi-u-edit-tag-checkbox');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixEditTagUiCommon);
    } else {
        fixEditTagUiCommon();
    }
})();
/* STEP5P_EDIT_TAG_UI_COMMON_FIX_END */


/* STEP5Q_EDIT_PAGE_MOBILE_UI_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function getConfig() {
        const path = pathKey();

        if (/^\/admin\/works\/[^/]+\/edit$/.test(path)) {
            return {
                bodyClass: 'oshi-u-work-edit-mobile',
                title: '作品編集',
                formActionIncludes: '/admin/works/',
                bottomLinks: [
                    { href: '/admin/works', label: '作品一覧へ戻る' }
                ]
            };
        }

        if (/^\/admin\/characters\/[^/]+\/edit$/.test(path)) {
            return {
                bodyClass: 'oshi-u-character-edit-mobile',
                title: 'キャラクター編集',
                formActionIncludes: '/admin/characters/',
                bottomLinks: [
                    { href: '/admin/characters', label: 'キャラクター一覧へ戻る' }
                ]
            };
        }

        if (/^\/admin\/character-relationships\/[^/]+\/edit$/.test(path)) {
            return {
                bodyClass: 'oshi-u-relationship-edit-mobile',
                title: '関係性編集',
                formActionIncludes: '/admin/character-relationships/',
                bottomLinks: [
                    { href: '/admin/character-relationships', label: '関係性一覧へ戻る' },
                    { href: '/admin/characters', label: 'キャラクター管理へ' }
                ]
            };
        }

        return null;
    }

    const OLD_NAV_WORDS = [
        'ダッシュボード',
        '承認待ち',
        'スタッフ申請',
        '作品管理',
        'キャラクター管理',
        '関係性管理',
        'タグ管理',
        'お問い合わせ受信箱',
        '公開ページ',
        'プロフィール設定'
    ];

    function applyEditMobileUi() {
        if (!isMobile()) return;

        const config = getConfig();
        if (!config) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-edit-mobile-ui', config.bodyClass);

        const forms = Array.from(main.querySelectorAll('form[action]'));

        const editForm = forms.find((form) => {
            const method = (form.getAttribute('method') || '').toLowerCase();
            const action = form.getAttribute('action') || '';

            return method === 'post' && action.includes(config.formActionIncludes) && !action.includes('bulk-action');
        }) || forms.find((form) => {
            return form.querySelector('input[name="_method"][value="PUT"], input[name="_method"][value="PATCH"]');
        });

        if (!editForm) return;

        editForm.classList.add('oshi-u-edit-mobile-form');

        const formCard =
            editForm.closest('.oshi-card') ||
            editForm.closest('.oshi-form-card') ||
            editForm.closest('.rounded') ||
            editForm.parentElement;

        if (!formCard) return;

        formCard.classList.add('oshi-u-edit-mobile-card');

        /*
         * 旧タイトル・旧ナビ・上部戻るボタンを非表示。
         * formを含む要素は消さない。
         */
        Array.from(main.querySelectorAll('div, nav, section, header')).forEach((el) => {
            if (el === formCard || el.contains(formCard) || formCard.contains(el)) return;
            if (el.querySelector('form, input, select, textarea')) return;

            const text = textOf(el);
            const hasOldNav = OLD_NAV_WORDS.some((word) => text.includes(word));
            const hasBack = text.includes('一覧へ') || text.includes('詳細へ') || text.includes('戻る');
            const hasOldTitle = text.includes('作品編集') || text.includes('キャラクター編集') || text.includes('関係性編集');

            if (hasOldNav || hasBack || hasOldTitle) {
                el.classList.add('oshi-u-edit-mobile-hidden-block');
            }
        });

        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);
            const href = link.getAttribute('href') || '';

            const isTopBack =
                text.includes('一覧へ') ||
                text.includes('詳細へ') ||
                text.includes('戻る') ||
                text.includes('キャラクター管理へ');

            const isOldNav = OLD_NAV_WORDS.includes(text);

            if (isTopBack || isOldNav || href === '/admin/dashboard') {
                link.classList.add('oshi-u-edit-mobile-hidden-link');
            }
        });

        /*
         * タイトル重複を避けて、フォームカード内にタイトルを1つだけ追加。
         */
        Array.from(formCard.querySelectorAll('.oshi-u-edit-mobile-title')).forEach((title) => {
            title.remove();
        });

        const title = document.createElement('h2');
        title.className = 'oshi-u-edit-mobile-title';
        title.textContent = config.title;
        formCard.prepend(title);

        /*
         * フォームカードをmain上部へ移動。
         */
        if (main.firstElementChild !== formCard) {
            main.insertBefore(formCard, main.firstElementChild);
        }

        /*
         * 保存ボタン統一。
         */
        Array.from(editForm.querySelectorAll('button[type="submit"], input[type="submit"]')).forEach((button) => {
            button.classList.add('oshi-u-edit-mobile-submit');
        });

        /*
         * 最下部戻るボタンを作り直す。
         */
        Array.from(main.querySelectorAll('.oshi-u-edit-mobile-bottom-actions')).forEach((el) => {
            el.remove();
        });

        const bottom = document.createElement('div');
        bottom.className = 'oshi-u-edit-mobile-bottom-actions';

        config.bottomLinks.forEach((item) => {
            const link = document.createElement('a');
            link.href = item.href;
            link.className = 'oshi-u-edit-mobile-bottom-link';
            link.textContent = item.label;
            bottom.appendChild(link);
        });

        main.appendChild(bottom);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyEditMobileUi);
    } else {
        applyEditMobileUi();
    }
})();
/* STEP5Q_EDIT_PAGE_MOBILE_UI_END */


/* STEP5R_EXTRA_ADMIN_MOBILE_CARDS_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function pathKey() {
        return window.location.pathname.replace(/\/$/, '');
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    const CONFIGS = {
        '/admin/contributor-applications': {
            bodyClass: 'oshi-u-contributor-applications-mobile',
            titlePatterns: ['スタッフ申請', '申請', '登用'],
            preferredLabels: {
                0: '申請者',
                1: 'メール',
                2: 'Discord',
                3: '状態',
                4: '申請日',
                5: '操作'
            }
        },
        '/admin/review-requests': {
            bodyClass: 'oshi-u-review-requests-mobile',
            titlePatterns: ['承認待ち', 'レビュー', '申請'],
            preferredLabels: {
                0: '対象',
                1: '種類',
                2: '申請者',
                3: '状態',
                4: '申請日',
                5: '操作'
            }
        },
        '/admin/contact-messages': {
            bodyClass: 'oshi-u-contact-messages-mobile',
            titlePatterns: ['お問い合わせ', '問い合わせ', '受信箱'],
            preferredLabels: {
                0: '送信者',
                1: 'メール',
                2: '件名',
                3: '状態',
                4: '受信日',
                5: '操作'
            }
        }
    };

    function cloneCell(cell) {
        const wrap = document.createElement('div');

        if (!cell) {
            wrap.textContent = '未設定';
            return wrap;
        }

        Array.from(cell.childNodes).forEach((node) => {
            wrap.appendChild(node.cloneNode(true));
        });

        if (!textOf(wrap) && !wrap.querySelector('input, select, textarea, button, a, form')) {
            wrap.textContent = '未設定';
        }

        return wrap;
    }

    function makeRow(label, content, role) {
        const row = document.createElement('div');
        row.className = 'oshi-u-extra-card-row';

        if (role) {
            row.classList.add('is-' + role);
        }

        const labelEl = document.createElement('div');
        labelEl.className = 'oshi-u-extra-card-label';
        labelEl.textContent = label;

        const valueEl = document.createElement('div');
        valueEl.className = 'oshi-u-extra-card-value';

        if (content instanceof HTMLElement) {
            valueEl.appendChild(content);
        } else {
            valueEl.textContent = content || '未設定';
        }

        row.appendChild(labelEl);
        row.appendChild(valueEl);

        return row;
    }

    function cellHasAction(cell) {
        if (!cell) return false;

        if (cell.querySelector('a, button, form')) {
            return true;
        }

        const text = textOf(cell);
        return ['詳細', '確認', '表示', '編集', '削除', '承認', '却下', '登用', '開始', '未読'].some((word) => text.includes(word));
    }

    function makeActions(cells, actionIndex) {
        const actions = document.createElement('div');
        actions.className = 'oshi-u-extra-card-actions';

        const actionCell = cells[actionIndex] || cells.find(cellHasAction);

        if (actionCell) {
            actions.appendChild(cloneCell(actionCell));
        }

        if (!textOf(actions) && !actions.querySelector('a, button, form')) {
            actions.textContent = '未設定';
        }

        return actions;
    }

    function buildCards(main, config) {
        const table = main.querySelector('table');

        if (!table || table.dataset.oshiExtraCardsReady === '1') {
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map((th, index) => {
            return textOf(th) || config.preferredLabels[index] || '項目';
        });

        const rows = Array.from(table.querySelectorAll('tbody tr'));

        if (!headers.length || !rows.length) {
            return;
        }

        const source = table.closest('.oshi-table-wrap') || table.closest('.overflow-x-auto') || table.parentElement || table;
        source.classList.add('oshi-u-extra-table-source');

        const list = document.createElement('div');
        list.className = 'oshi-u-extra-card-list';

        rows.forEach((tr) => {
            const cells = Array.from(tr.children);
            if (!cells.length) return;

            const card = document.createElement('article');
            card.className = 'oshi-u-extra-card';

            let actionIndex = headers.findIndex((header) => header.includes('操作'));

            if (actionIndex < 0) {
                actionIndex = cells.findIndex(cellHasAction);
            }

            cells.forEach((cell, index) => {
                if (index === actionIndex) return;

                const label = headers[index] || config.preferredLabels[index] || '項目';
                const text = textOf(cell);

                if (!text && !cell.querySelector('input, select, textarea, button, a, form')) return;

                card.appendChild(makeRow(label, cloneCell(cell), label.includes('状態') ? 'status' : ''));
            });

            card.appendChild(makeRow('操作', makeActions(cells, actionIndex), 'actions'));

            list.appendChild(card);
        });

        source.insertAdjacentElement('beforebegin', list);
        table.dataset.oshiExtraCardsReady = '1';
    }

    function hideOldBodyNav(main) {
        const oldTexts = [
            'ダッシュボード',
            '承認待ち',
            'スタッフ申請',
            '作品管理',
            'キャラクター管理',
            '関係性管理',
            'タグ管理',
            'お問い合わせ受信箱',
            '公開ページ',
            'プロフィール設定'
        ];

        Array.from(main.querySelectorAll('a')).forEach((link) => {
            const text = textOf(link);
            if (oldTexts.includes(text)) {
                link.classList.add('oshi-u-extra-old-nav-hidden');
            }
        });
    }

    function applyExtraAdminCards() {
        if (!isMobile()) return;

        const config = CONFIGS[pathKey()];
        if (!config) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-extra-admin-mobile-cards', config.bodyClass);

        hideOldBodyNav(main);
        buildCards(main, config);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyExtraAdminCards);
    } else {
        applyExtraAdminCards();
    }
})();
/* STEP5R_EXTRA_ADMIN_MOBILE_CARDS_END */


/* STEP5S_REVIEW_REQUESTS_ALL_TABLES_CARDS_START */
(function () {
    function isMobile() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function isReviewRequestsPage() {
        return window.location.pathname.replace(/\/$/, '') === '/admin/review-requests';
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function cloneCell(cell) {
        const wrap = document.createElement('div');

        if (!cell) {
            wrap.textContent = '未設定';
            return wrap;
        }

        Array.from(cell.childNodes).forEach((node) => {
            wrap.appendChild(node.cloneNode(true));
        });

        if (!textOf(wrap) && !wrap.querySelector('a, button, form, input, select, textarea')) {
            wrap.textContent = '未設定';
        }

        return wrap;
    }

    function makeRow(label, content, role) {
        const row = document.createElement('div');
        row.className = 'oshi-u-review-card-row';

        if (role) {
            row.classList.add('is-' + role);
        }

        const labelEl = document.createElement('div');
        labelEl.className = 'oshi-u-review-card-label';
        labelEl.textContent = label;

        const valueEl = document.createElement('div');
        valueEl.className = 'oshi-u-review-card-value';

        if (content instanceof HTMLElement) {
            valueEl.appendChild(content);
        } else {
            valueEl.textContent = content || '未設定';
        }

        row.appendChild(labelEl);
        row.appendChild(valueEl);

        return row;
    }

    function cellHasAction(cell) {
        if (!cell) return false;

        if (cell.querySelector('a, button, form')) {
            return true;
        }

        const text = textOf(cell);
        return ['承認', '却下', '詳細', '確認', '編集', '削除', '反映'].some((word) => text.includes(word));
    }

    function makeActions(cell) {
        const actions = document.createElement('div');
        actions.className = 'oshi-u-review-card-actions';

        if (cell) {
            actions.appendChild(cloneCell(cell));
        }

        if (!textOf(actions) && !actions.querySelector('a, button, form')) {
            actions.textContent = '未設定';
        }

        return actions;
    }

    function getSectionTitle(table) {
        const candidates = [];
        let current = table;

        for (let i = 0; i < 6; i++) {
            current = current.parentElement;
            if (!current) break;

            const heading = current.querySelector('h1, h2, h3, h4');
            if (heading && textOf(heading)) {
                candidates.push(textOf(heading));
            }
        }

        if (candidates.length) {
            return candidates[0];
        }

        return '';
    }

    function buildTableCards(table, tableIndex) {
        if (!table || table.dataset.oshiReviewCardsReady === '1') {
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map((th) => textOf(th) || '項目');
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        if (!headers.length || !rows.length) {
            return;
        }

        const source =
            table.closest('.oshi-table-wrap') ||
            table.closest('.overflow-x-auto') ||
            table.parentElement ||
            table;

        source.classList.add('oshi-u-review-table-source');

        const sectionTitle = getSectionTitle(table);

        const list = document.createElement('div');
        list.className = 'oshi-u-review-card-list';

        if (sectionTitle) {
            list.setAttribute('data-section-title', sectionTitle);
        }

        rows.forEach((tr) => {
            const cells = Array.from(tr.children);
            if (!cells.length) return;

            const card = document.createElement('article');
            card.className = 'oshi-u-review-card';

            const actionIndex = headers.findIndex((header) => header.includes('操作'));
            const fallbackActionIndex = cells.findIndex(cellHasAction);
            const actualActionIndex = actionIndex >= 0 ? actionIndex : fallbackActionIndex;

            cells.forEach((cell, index) => {
                if (index === actualActionIndex) return;

                const label = headers[index] || '項目';
                const content = cloneCell(cell);
                const role = label.includes('状態') ? 'status' : '';

                card.appendChild(makeRow(label, content, role));
            });

            if (actualActionIndex >= 0) {
                card.appendChild(makeRow('操作', makeActions(cells[actualActionIndex]), 'actions'));
            }

            list.appendChild(card);
        });

        source.insertAdjacentElement('beforebegin', list);
        table.dataset.oshiReviewCardsReady = '1';
    }

    function applyReviewAllTablesCards() {
        if (!isMobile()) return;
        if (!isReviewRequestsPage()) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        document.body.classList.add('oshi-u-review-all-tables-cards');

        Array.from(main.querySelectorAll('table')).forEach((table, index) => {
            buildTableCards(table, index);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyReviewAllTablesCards);
    } else {
        applyReviewAllTablesCards();
    }
})();
/* STEP5S_REVIEW_REQUESTS_ALL_TABLES_CARDS_END */


/* STEP5U_RELATIONSHIP_FLASH_DUPLICATE_FINAL_START */
(function () {
    function isRelationshipIndexPage() {
        return window.location.pathname.replace(/\/$/, '') === '/admin/character-relationships';
    }

    function textOf(element) {
        return (element?.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function fixRelationshipFlashDuplicate() {
        if (!isRelationshipIndexPage()) return;

        const main = document.querySelector('.oshi-admin-main') || document.querySelector('main');
        if (!main) return;

        const messageText = 'キャラクター関係性を更新しました。';
        const addText = '関係性を追加しました。';

        const candidates = Array.from(main.querySelectorAll('div')).filter((el) => {
            const text = textOf(el);
            return text === messageText || text === addText;
        });

        if (candidates.length <= 1) return;

        /*
         * 下側の見やすいメッセージを残し、上側の共通フラッシュを削除。
         */
        candidates.slice(0, -1).forEach((el) => {
            el.classList.add('oshi-u-relationship-flash-duplicate-hidden');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixRelationshipFlashDuplicate);
    } else {
        fixRelationshipFlashDuplicate();
    }
})();
/* STEP5U_RELATIONSHIP_FLASH_DUPLICATE_FINAL_END */

