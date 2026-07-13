@if (
    auth()->check()
    && auth()->user()?->isStaff()
    && ! auth()->user()?->canManageAllAdminFeatures()
    && request()->routeIs('admin.works.index', 'admin.characters.index', 'admin.character-relationships.index', 'admin.tags.index')
)
    {{-- STAFF_ADMIN_MOBILE_UI_FIX --}}
    <style>
        @media (max-width: 767px) {
            body.oshi-staff-admin-mobile-ui {
                overflow-x: hidden;
            }

            body.oshi-staff-admin-mobile-ui main,
            body.oshi-staff-admin-mobile-ui .p-6 {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            body.oshi-staff-admin-mobile-ui .p-6 {
                padding: 16px !important;
            }

            body.oshi-staff-admin-mobile-ui .oshi-card {
                border-radius: 24px !important;
                padding: 20px !important;
                overflow: visible !important;
            }

            body.oshi-staff-admin-mobile-ui .overflow-x-auto,
            body.oshi-staff-admin-mobile-ui .oshi-table-wrap {
                overflow-x: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
            }

            body.oshi-staff-admin-mobile-ui .overflow-x-auto::-webkit-scrollbar,
            body.oshi-staff-admin-mobile-ui .oshi-table-wrap::-webkit-scrollbar {
                display: none !important;
            }

            body.oshi-staff-admin-mobile-ui table.oshi-staff-mobile-source-table {
                display: none !important;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-table-shell {
                border: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-table-shell > .overflow-x-auto,
            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-table-shell > .oshi-table-wrap {
                border: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-list {
                display: grid;
                gap: 14px;
                margin-top: 18px;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card {
                border: 1px solid #E2E8F0;
                border-radius: 22px;
                background: #FFFFFF;
                padding: 16px;
                box-shadow: 0 8px 20px rgba(45, 55, 72, 0.04);
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-row {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 10px;
                align-items: start;
                padding: 9px 0;
                border-bottom: 1px solid #EDF2F7;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-row:last-child {
                border-bottom: 0;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-label {
                color: #A0AEC0;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.5;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-value {
                color: #2D3748;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.7;
                word-break: break-word;
            }

            body.oshi-staff-admin-mobile-ui .oshi-staff-mobile-card-actions {
                display: grid;
                gap: 10px;
                margin-top: 14px;
            }

            body.oshi-staff-admin-mobile-ui .oshi-btn,
            body.oshi-staff-admin-mobile-ui button,
            body.oshi-staff-admin-mobile-ui a.oshi-btn {
                width: 100%;
                max-width: 100%;
                justify-content: center;
                text-align: center;
            }

            body.oshi-staff-admin-mobile-ui form {
                max-width: 100%;
            }

            body.oshi-staff-admin-mobile-ui input[type="text"],
            body.oshi-staff-admin-mobile-ui input[type="search"],
            body.oshi-staff-admin-mobile-ui input[type="email"],
            body.oshi-staff-admin-mobile-ui input[type="password"],
            body.oshi-staff-admin-mobile-ui select,
            body.oshi-staff-admin-mobile-ui textarea {
                width: 100% !important;
                max-width: 100% !important;
            }

            body.oshi-staff-admin-mobile-ui input[type="checkbox"] {
                width: 18px !important;
                height: 18px !important;
                min-width: 18px !important;
                min-height: 18px !important;
                border-radius: 3px !important;
                flex: 0 0 auto !important;
                -webkit-appearance: checkbox !important;
                appearance: checkbox !important;
            }

            body.oshi-staff-admin-mobile-ui label:has(input[type="checkbox"]),
            body.oshi-staff-admin-mobile-ui .checkbox-label,
            body.oshi-staff-admin-mobile-ui .form-checkbox-label {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
                line-height: 1.5 !important;
            }

            body.oshi-staff-admin-mobile-ui label:has(input[type="checkbox"]) span,
            body.oshi-staff-admin-mobile-ui label:has(input[type="checkbox"]) div {
                display: inline !important;
                margin: 0 !important;
            }

            body.oshi-staff-admin-mobile-ui .grid {
                min-width: 0;
            }

            body.oshi-staff-admin-mobile-ui .flex {
                min-width: 0;
            }

            body.oshi-staff-admin-mobile-ui.oshi-staff-tags-mobile-ui form[action*="/admin/tags"] {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                margin-bottom: 18px !important;
            }

            body.oshi-staff-admin-mobile-ui.oshi-staff-tags-mobile-ui form[action*="/admin/tags"] .grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
        }

        @media (min-width: 768px) {
            .oshi-staff-mobile-card-list {
                display: none !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const targetPaths = [
                '/admin/works',
                '/admin/characters',
                '/admin/character-relationships',
                '/admin/tags',
            ];

            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (! targetPaths.includes(currentPath)) {
                return;
            }

            document.body.classList.add('oshi-staff-admin-mobile-ui');

            if (currentPath === '/admin/tags') {
                document.body.classList.add('oshi-staff-tags-mobile-ui');
            }

            if (! window.matchMedia('(max-width: 767px)').matches) {
                return;
            }

            document.querySelectorAll('.oshi-staff-mobile-card-list').forEach(function (list) {
                list.remove();
            });

            function normalizeText(text) {
                return (text || '').replace(/\s+/g, ' ').trim();
            }

            function getHeaders(table) {
                return Array.from(table.querySelectorAll('thead th')).map(function (th, index) {
                    const text = normalizeText(th.textContent);

                    if (! text) {
                        return '';
                    }

                    if (text === '呼ばれ方') {
                        return '呼び方';
                    }

                    return text || `項目${index + 1}`;
                });
            }

            function isActionCell(header, cell) {
                if (header === '操作') {
                    return true;
                }

                return !! cell.querySelector('a, button, form');
            }

            function createCardFromRow(row, headers) {
                const cells = Array.from(row.children);
                const card = document.createElement('div');
                card.className = 'oshi-staff-mobile-card';

                const actions = document.createElement('div');
                actions.className = 'oshi-staff-mobile-card-actions';

                cells.forEach(function (cell, index) {
                    const header = headers[index] || '';
                    const clone = cell.cloneNode(true);

                    clone.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                        checkbox.remove();
                    });

                    const valueText = normalizeText(clone.textContent);
                    const hasControls = clone.querySelector('a, button, form');

                    if (! header && ! valueText && ! hasControls) {
                        return;
                    }

                    if (isActionCell(header, clone)) {
                        Array.from(clone.querySelectorAll('a, button, form')).forEach(function (node) {
                            if (node.tagName === 'FORM') {
                                node.classList.add('w-full');

                                const button = node.querySelector('button');

                                if (button) {
                                    button.classList.add('w-full');
                                }

                                actions.appendChild(node);
                            } else {
                                node.classList.add('w-full');
                                actions.appendChild(node);
                            }
                        });

                        return;
                    }

                    if (! valueText && ! clone.querySelector('.inline-flex, span')) {
                        return;
                    }

                    const rowEl = document.createElement('div');
                    rowEl.className = 'oshi-staff-mobile-card-row';

                    const labelEl = document.createElement('div');
                    labelEl.className = 'oshi-staff-mobile-card-label';
                    labelEl.textContent = header || '項目';

                    const valueEl = document.createElement('div');
                    valueEl.className = 'oshi-staff-mobile-card-value';
                    valueEl.innerHTML = clone.innerHTML;

                    rowEl.appendChild(labelEl);
                    rowEl.appendChild(valueEl);
                    card.appendChild(rowEl);
                });

                if (actions.children.length > 0) {
                    card.appendChild(actions);
                }

                return card;
            }

            function findTableShell(table) {
                const scrollWrapper = table.closest('.overflow-x-auto');
                const tableWrap = table.closest('.oshi-table-wrap');

                if (scrollWrapper) {
                    return scrollWrapper;
                }

                if (tableWrap) {
                    return tableWrap;
                }

                return table;
            }

            function ensureTagsSearchBeforeCards() {
                if (currentPath !== '/admin/tags') {
                    return;
                }

                const cards = document.querySelector('.oshi-staff-mobile-card-list');

                if (! cards) {
                    return;
                }

                const forms = Array.from(document.querySelectorAll('form[action*="/admin/tags"]'));

                const searchForm = forms.find(function (form) {
                    const text = normalizeText(form.textContent);
                    const hasSearchInput = form.querySelector('input[name="keyword"], input[name="q"], select[name="type"], select[name="status"]');
                    const hasSearchButton = text.includes('検索') || !! form.querySelector('button[type="submit"]');

                    return hasSearchInput && hasSearchButton;
                });

                if (! searchForm) {
                    return;
                }

                cards.parentElement.insertBefore(searchForm, cards);

                searchForm.style.display = 'block';
                searchForm.style.visibility = 'visible';
                searchForm.style.opacity = '1';
            }

            function convertTable(table) {
                if (table.dataset.staffMobileConverted === '1') {
                    return;
                }

                const tbodyRows = Array.from(table.querySelectorAll('tbody tr'));

                if (tbodyRows.length === 0) {
                    return;
                }

                table.dataset.staffMobileConverted = '1';
                table.classList.add('oshi-staff-mobile-source-table');

                const headers = getHeaders(table);
                const list = document.createElement('div');
                list.className = 'oshi-staff-mobile-card-list';

                tbodyRows.forEach(function (row) {
                    const emptyText = normalizeText(row.textContent);
                    const hasColspan = row.querySelector('[colspan]');

                    if (hasColspan && emptyText) {
                        const card = document.createElement('div');
                        card.className = 'oshi-staff-mobile-card';
                        card.textContent = emptyText;
                        list.appendChild(card);
                        return;
                    }

                    list.appendChild(createCardFromRow(row, headers));
                });

                const shell = findTableShell(table);
                shell.classList.add('oshi-staff-mobile-table-shell');
                shell.insertAdjacentElement('afterend', list);
            }

            document.querySelectorAll('table').forEach(convertTable);

            ensureTagsSearchBeforeCards();

            document.querySelectorAll('label').forEach(function (label) {
                const checkbox = label.querySelector('input[type="checkbox"]');

                if (! checkbox) {
                    return;
                }

                label.style.display = 'inline-flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
            });
        });
    </script>
    {{-- /STAFF_ADMIN_MOBILE_UI_FIX --}}
@endif
