<div
    class="hidden"
    data-work-character-search-template
>
    <div
        class="mb-5 rounded-2xl border border-gray-200 bg-white p-4"
        data-work-character-search
    >
        <label
            for="work-character-search-input"
            class="block text-sm font-bold text-gray-800"
        >
            この作品のキャラクターを検索
        </label>

        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
            <div class="relative min-w-0 flex-1">
                <input
                    id="work-character-search-input"
                    type="search"
                    inputmode="search"
                    autocomplete="off"
                    placeholder="名前・所属・学年・役職などを入力"
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    data-work-character-search-input
                >

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 hidden px-3 text-gray-500 hover:text-gray-800"
                    aria-label="検索文字を消去"
                    data-work-character-search-clear
                >
                    ×
                </button>
            </div>

            <div
                class="flex min-h-11 items-center rounded-xl bg-gray-50 px-4 text-sm text-gray-700"
                aria-live="polite"
                data-work-character-search-count
            ></div>
        </div>

        <p class="mt-2 text-xs leading-6 text-gray-500">
            ひらがな・カタカナ、全角・半角、空白や記号の違いを吸収し、
            一部の文字や近い表記でも検索できます。
        </p>

        <p
            class="mt-4 hidden rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900"
            data-work-character-search-empty
        >
            該当するキャラクターが見つかりませんでした。
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const template = document.querySelector(
        '[data-work-character-search-template]'
    );

    if (!template) {
        return;
    }

    const characterLinks = Array.from(
        document.querySelectorAll(
            'a[href*="/characters/"], a[href*="characters/"]'
        )
    ).filter((link) => {
        return !link.closest(
            'header, nav, footer, [data-work-character-search]'
        );
    });

    if (characterLinks.length === 0) {
        template.remove();
        return;
    }

    const heading = Array.from(
        document.querySelectorAll('h2, h3, h4')
    ).find((element) => {
        const text = (element.textContent || '').trim();
        return /キャラクター/.test(text);
    });

    if (!heading) {
        template.remove();
        return;
    }

    const section = heading.closest('section, article, .oshi-card')
        || heading.parentElement;

    if (!section) {
        template.remove();
        return;
    }

    const sectionLinks = characterLinks.filter(
        (link) => section.contains(link)
    );

    if (sectionLinks.length === 0) {
        template.remove();
        return;
    }

    const findCard = (link) => {
        const preferred = link.closest(
            '[data-character-card], article, li, .character-card'
        );

        if (preferred && section.contains(preferred)) {
            return preferred;
        }

        let current = link;

        while (
            current.parentElement
            && current.parentElement !== section
        ) {
            const parent = current.parentElement;
            const links = parent.querySelectorAll(
                'a[href*="/characters/"], a[href*="characters/"]'
            );

            if (links.length === 1) {
                current = parent;
                continue;
            }

            break;
        }

        return current;
    };

    const cards = Array.from(
        new Set(sectionLinks.map(findCard))
    ).filter(Boolean);

    if (cards.length === 0) {
        template.remove();
        return;
    }

    const search = template.firstElementChild.cloneNode(true);
    heading.insertAdjacentElement('afterend', search);
    template.remove();

    const input = search.querySelector(
        '[data-work-character-search-input]'
    );
    const clear = search.querySelector(
        '[data-work-character-search-clear]'
    );
    const count = search.querySelector(
        '[data-work-character-search-count]'
    );
    const empty = search.querySelector(
        '[data-work-character-search-empty]'
    );

    const toHiragana = (value) => {
        return value.replace(/[\u30A1-\u30F6]/g, (char) => {
            return String.fromCharCode(
                char.charCodeAt(0) - 0x60
            );
        });
    };

    const normalize = (value) => {
        return toHiragana(
            String(value || '')
                .normalize('NFKC')
                .toLocaleLowerCase('ja')
        ).replace(
            /[\s\u3000・･、，,。．.\/／\-ー_＿:：;；'"“”‘’()[\]{}「」『』【】〈〉《》！？!?]+/g,
            ''
        );
    };

    const levenshtein = (left, right, limit) => {
        if (Math.abs(left.length - right.length) > limit) {
            return limit + 1;
        }

        let previous = Array.from(
            { length: right.length + 1 },
            (_, index) => index
        );

        for (let i = 1; i <= left.length; i += 1) {
            const current = [i];
            let rowMin = current[0];

            for (let j = 1; j <= right.length; j += 1) {
                const cost = left[i - 1] === right[j - 1]
                    ? 0
                    : 1;

                current[j] = Math.min(
                    previous[j] + 1,
                    current[j - 1] + 1,
                    previous[j - 1] + cost
                );

                rowMin = Math.min(rowMin, current[j]);
            }

            if (rowMin > limit) {
                return limit + 1;
            }

            previous = current;
        }

        return previous[right.length];
    };

    const isSubsequence = (needle, haystack) => {
        let index = 0;

        for (const char of haystack) {
            if (char === needle[index]) {
                index += 1;
            }

            if (index === needle.length) {
                return true;
            }
        }

        return false;
    };

    const fuzzyMatch = (query, text) => {
        if (query === '') {
            return true;
        }

        if (text.includes(query)) {
            return true;
        }

        if (query.length >= 2 && isSubsequence(query, text)) {
            return true;
        }

        if (query.length < 3) {
            return false;
        }

        const limit = Math.max(
            1,
            Math.min(3, Math.floor(query.length * 0.3))
        );

        const words = String(text)
            .split(/[^ぁ-んa-z0-9]+/u)
            .filter(Boolean);

        if (
            words.some(
                (word) => levenshtein(query, word, limit) <= limit
            )
        ) {
            return true;
        }

        const windowMin = Math.max(1, query.length - limit);
        const windowMax = Math.min(
            text.length,
            query.length + limit
        );

        for (
            let size = windowMin;
            size <= windowMax;
            size += 1
        ) {
            for (
                let start = 0;
                start + size <= text.length;
                start += 1
            ) {
                const part = text.slice(start, start + size);

                if (
                    levenshtein(query, part, limit) <= limit
                ) {
                    return true;
                }
            }
        }

        return false;
    };

    const entries = cards.map((card) => {
        return {
            card,
            text: normalize(card.textContent),
        };
    });

    const render = () => {
        const query = normalize(input.value);
        let visible = 0;

        entries.forEach(({ card, text }) => {
            const matched = fuzzyMatch(query, text);
            card.classList.toggle('hidden', !matched);

            if (matched) {
                visible += 1;
            }
        });

        count.textContent = query === ''
            ? `${entries.length}人`
            : `${visible} / ${entries.length}人`;

        clear.classList.toggle(
            'hidden',
            input.value.length === 0
        );

        empty.classList.toggle(
            'hidden',
            visible !== 0
        );
    };

    input.addEventListener('input', render);

    clear.addEventListener('click', () => {
        input.value = '';
        input.focus();
        render();
    });

    search.classList.remove('hidden');
    render();
});
</script>
