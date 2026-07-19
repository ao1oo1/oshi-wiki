<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Oshi-Wiki 管理画面
        </h2>
    </x-slot>

    <div class="p-6">

{{-- STAFF_DASHBOARD_NOTICE --}}
@if (auth()->user()?->isStaff())
    <div class="mb-6 rounded-3xl border border-[#FED7E2] bg-[#FFF5F7] p-6 shadow-sm">
        <div class="mb-4">
            <p class="inline-flex rounded-full bg-white px-4 py-2 text-sm font-bold text-[#2D3748]">
                管理スタッフの方へ
            </p>
        </div>

        <h3 class="mb-4 text-xl font-bold text-[#2D3748]">
            コントリビューター登録ありがとうございます。
        </h3>

        <div class="space-y-4 leading-8 text-[#4A5568]">
            <p>
                Oshi-Wikiでは、作品・キャラクター・関係性などの情報を、できるだけ客観的で信頼できる形で整理しています。
                情報を登録する際は、公式サイト、公式ファンブック、設定資料集、公式ガイドブックなど、信ぴょう性のある資料をもとに入力してください。
            </p>

            <p>
                登録・編集した情報は、すぐには公開されません。
                管理者が内容を確認したうえで、問題がないものから順次公開します。
            </p>

            <p>
                登録したい作品がある場合や、機能の使いにくさ、改善案、不具合などがある場合は、管理者へフォームからご連絡ください。
            </p>
        </div>
    </div>
@endif
{{-- /STAFF_DASHBOARD_NOTICE --}}

<div class="mx-auto max-w-6xl">
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <a
                    href="{{ route('admin.works.index') }}"
                    class="block oshi-card"
                    style="text-decoration:none;color:inherit;"
                >
                    <p class="mb-2 text-sm text-gray-500">作品</p>
                    <p class="text-3xl font-bold">{{ $workCount }}</p>
                    <p class="mt-3 text-blue-600">作品管理へ</p>
                </a>

                <a
                    href="{{ route('admin.characters.index') }}"
                    class="block oshi-card"
                    style="text-decoration:none;color:inherit;"
                >
                    <p class="mb-2 text-sm text-gray-500">キャラクター</p>
                    <p class="text-3xl font-bold">{{ $characterCount }}</p>
                    <p class="mt-3 text-blue-600">キャラクター管理へ</p>
                </a>

                <a
                    href="{{ route('admin.character-relationships.index') }}"
                    class="block oshi-card"
                    style="text-decoration:none;color:inherit;"
                >
                    <p class="mb-2 text-sm text-gray-500">関係性</p>
                    <p class="text-3xl font-bold">{{ $relationshipCount }}</p>
                    <p class="mt-3 text-blue-600">関係性管理へ</p>
                </a>
            </div>

            <div class="mb-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('public.works.index') }}"
                    target="_blank"
                    style="display:inline-block;background:#2D3748;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    公開ページを見る
                </a>

                <a
                    href="{{ route('admin.works.index') }}"
                    style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品を登録
                </a>

                <a
                    href="{{ route('admin.characters.create') }}"
                    style="display:inline-block;background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    キャラクターを登録
                </a>

                <a
                    href="{{ route('admin.character-relationships.create') }}"
                    style="display:inline-block;background:#9333ea;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    関係性を登録
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="oshi-card">
                    <h3 class="mb-4 text-lg font-semibold">
                        最近登録した作品
                    </h3>

                    @if ($latestWorks->count())
                        <div class="space-y-3">
                            @foreach ($latestWorks as $work)
                                <div class="border-b pb-3">
                                    <a
                                        href="{{ route('admin.works.show', $work) }}"
                                        class="font-semibold text-blue-600"
                                    >
                                        {{ $work->title }}
                                    </a>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $work->created_at?->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">
                            まだ作品が登録されていません。
                        </p>
                    @endif
                </div>

                <div class="oshi-card">
                    <h3 class="mb-4 text-lg font-semibold">
                        最近登録したキャラクター
                    </h3>

                    @if ($latestCharacters->count())
                        <div class="space-y-3">
                            @foreach ($latestCharacters as $character)
                                <div class="border-b pb-3">
                                    <a
                                        href="{{ route('admin.characters.show', $character) }}"
                                        class="font-semibold text-blue-600"
                                    >
                                        {{ $character->name }}
                                    </a>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $character->work?->title }} /
                                        {{ $character->created_at?->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">
                            まだキャラクターが登録されていません。
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
