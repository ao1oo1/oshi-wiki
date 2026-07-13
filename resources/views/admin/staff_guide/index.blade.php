<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            管理スタッフ向けご案内
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.navigation')

        <div class="mx-auto max-w-4xl">
            <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
                <div class="mb-8">
                    <p class="mb-3 inline-flex rounded-full bg-[#FFF5F7] px-4 py-2 text-sm font-bold text-[#2D3748]">
                        はじめに
                    </p>

                    <h1 class="text-2xl font-bold text-[#2D3748] md:text-3xl">
                        管理スタッフ向けご案内
                    </h1>

                    <p class="mt-4 leading-8 text-[#4A5568]">
                        このたびは、Oshi-Wikiのコントリビューターにご登録いただき、誠にありがとうございます。
                        Oshi-Wikiでは、作品・キャラクター・関係性などの情報を、できるだけ客観的で信頼できる形で整理することを大切にしています。
                    </p>
                </div>

                <div class="space-y-6">
                    <section class="rounded-3xl bg-[#F7FAFC] p-5">
                        <h2 class="mb-3 text-xl font-bold text-[#2D3748]">
                            情報登録時のお願い
                        </h2>

                        <p class="leading-8 text-[#4A5568]">
                            情報を登録する際は、公式サイト、公式ファンブック、設定資料集、公式ガイドブックなど、
                            信ぴょう性のある資料をもとに入力してください。
                            個人の解釈や推測、二次創作上の設定などは、公式情報と混同しないようご注意ください。
                        </p>
                    </section>

                    <section class="rounded-3xl bg-[#F7FAFC] p-5">
                        <h2 class="mb-3 text-xl font-bold text-[#2D3748]">
                            登録内容の公開について
                        </h2>

                        <p class="leading-8 text-[#4A5568]">
                            登録・編集した情報は、すぐには公開されません。
                            管理者が内容を確認したうえで、問題がないものから順次公開します。
                            反映までお時間をいただく場合がありますので、あらかじめご了承ください。
                        </p>
                    </section>

                    <section class="rounded-3xl bg-[#F7FAFC] p-5">
                        <h2 class="mb-3 text-xl font-bold text-[#2D3748]">
                            登録したい作品がある場合
                        </h2>

                        <p class="leading-8 text-[#4A5568]">
                            登録したい作品がある場合は、管理者へフォームからお伝えください。
                            作品追加の可否や登録方針を確認したうえで、必要に応じて対応します。
                        </p>
                    </section>

                    <section class="rounded-3xl bg-[#F7FAFC] p-5">
                        <h2 class="mb-3 text-xl font-bold text-[#2D3748]">
                            改善要望・不具合報告について
                        </h2>

                        <p class="leading-8 text-[#4A5568]">
                            機能が使いにくい、改善したい点がある、バグを見つけたなどの場合も、
                            管理者へフォームからご連絡ください。
                            いただいた内容は、今後の改善の参考にさせていただきます。
                        </p>
                    </section>

                    <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF5F7] p-5">
                        <h2 class="mb-3 text-xl font-bold text-[#2D3748]">
                            ご利用時の注意
                        </h2>

                        <ul class="list-disc space-y-2 pl-5 leading-8 text-[#4A5568]">
                            <li>一次創作者、公式、他の利用者に迷惑がかかる内容の登録はお控えください。</li>
                            <li>出典が不明な情報や、事実確認が難しい情報は無理に登録しないでください。</li>
                            <li>誤りに気づいた場合は、修正または管理者への連絡をお願いします。</li>
                            <li>公開前の内容も、できるだけ客観的で丁寧な記述を心がけてください。</li>
                        </ul>
                    </section>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="oshi-btn oshi-btn-sub">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
