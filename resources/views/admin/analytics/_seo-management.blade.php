<section
    id="seo-management"
    class="rounded-3xl border-4 border-[#FED7E2] bg-white p-6 shadow-sm"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#2D3748]">SEO管理</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                サイト共通の検索表示情報とインデックス状況を管理します。
                この機能は最高管理者のみ利用できます。
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ $seoDiagnostics['sitemap_url'] }}"
                target="_blank"
                rel="noopener"
                class="oshi-btn oshi-btn-sub"
            >
                サイトマップを確認
            </a>
            <a
                href="{{ $seoDiagnostics['robots_url'] }}"
                target="_blank"
                rel="noopener"
                class="oshi-btn oshi-btn-sub"
            >
                robots.txtを確認
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['公開作品', $seoDiagnostics['published_works'], '件'],
            ['公開キャラクター', $seoDiagnostics['published_characters'], '件'],
            ['検索ワード未設定の作品', $seoDiagnostics['works_without_keywords'], '件'],
            ['検索ワード未設定のキャラ', $seoDiagnostics['characters_without_keywords'], '件'],
        ] as [$label, $value, $suffix])
            <article class="rounded-2xl bg-[#FFF7FA] p-4">
                <p class="text-sm font-bold text-[#718096]">
                    {{ $label }}
                </p>
                <p class="mt-2 text-3xl font-bold text-[#2D3748]">
                    {{ number_format($value) }}
                    <span class="text-sm">{{ $suffix }}</span>
                </p>
            </article>
        @endforeach
    </div>

    <form
        method="POST"
        action="{{ route('admin.analytics.seo.update') }}"
        class="mt-7 space-y-6"
    >
        @csrf
        @method('PATCH')

        <div class="grid gap-6 lg:grid-cols-2">
            <label class="block">
                <span class="oshi-label">サイトSEOタイトル</span>
                <input
                    type="text"
                    name="site_title"
                    value="{{ old('site_title', $seoSetting->site_title) }}"
                    maxlength="255"
                    class="oshi-input"
                >
            </label>

            <label class="block">
                <span class="oshi-label">Googleサイト確認コード</span>
                <input
                    type="text"
                    name="google_site_verification"
                    value="{{ old(
                        'google_site_verification',
                        $seoSetting->google_site_verification
                    ) }}"
                    maxlength="255"
                    class="oshi-input"
                    placeholder="QqGXYg2yOCNg..."
                >
                <span class="mt-2 block text-xs leading-6 text-[#718096]">
                    google-site-verification= を除いたコードだけを入力します。
                </span>
            </label>
        </div>

        <label class="block">
            <span class="oshi-label">共通description</span>
            <textarea
                name="site_description"
                rows="4"
                maxlength="500"
                class="oshi-input"
            >{{ old('site_description', $seoSetting->site_description) }}</textarea>
        </label>

        <label class="block">
            <span class="oshi-label">共通SEOワード</span>
            <textarea
                name="site_keywords"
                rows="4"
                maxlength="2000"
                class="oshi-input"
                placeholder="アニメ, 漫画, ゲーム, キャラクター"
            >{{ old('site_keywords', $seoSetting->site_keywords) }}</textarea>
        </label>

        <label class="block">
            <span class="oshi-label">共通OG画像URL</span>
            <input
                type="url"
                name="default_og_image_url"
                value="{{ old(
                    'default_og_image_url',
                    $seoSetting->default_og_image_url
                ) }}"
                maxlength="2000"
                class="oshi-input"
            >
        </label>

        <label class="inline-flex items-center gap-3 font-bold text-[#2D3748]">
            <input
                type="checkbox"
                name="append_site_name_to_titles"
                value="1"
                @checked(old(
                    'append_site_name_to_titles',
                    $seoSetting->append_site_name_to_titles
                ))
            >
            個別ページタイトルの末尾にサイト名を付ける
        </label>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">SEO運用のおすすめ</h3>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-7 text-[#718096]">
                <li>作品・キャラクターごとに略称、英語名、よく使われる呼び名を登録する</li>
                <li>無関係な人気語は登録せず、実際の表記揺れだけを設定する</li>
                <li>Search Consoleでサイトマップと主要ページを定期確認する</li>
                <li>descriptionはページ内容を正確に要約する</li>
            </ul>
        </div>

        <button type="submit" class="oshi-btn">
            SEO設定を保存
        </button>
    </form>
</section>
