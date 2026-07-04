<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            関係性登録
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-4xl">
            @include('admin.partials.navigation')


            <div class="mb-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.character-relationships.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    関係性一覧へ
                </a>

                <a
                    href="{{ route('admin.characters.index') }}"
                    style="display:inline-block;background:#6b7280;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    キャラクター管理へ
                </a>
            </div>

            <div class="mb-6 rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">
                    作品を選択
                </h3>

                <form method="GET" action="{{ route('admin.character-relationships.create') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="filter_work_id" class="mb-1 block font-medium">
                            作品
                        </label>

                        <select
                            id="filter_work_id"
                            name="work_id"
                            class="rounded border-gray-300"
                            required
                        >
                            <option value="">選択してください</option>
                            @foreach ($works as $work)
                                <option
                                    value="{{ $work->id }}"
                                    @selected(($selectedWorkId ?? '') == $work->id)
                                >
                                    {{ $work->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;"
                    >
                        この作品で登録へ進む
                    </button>
                </form>
            </div>

            @if ($selectedWorkId)
                <div class="rounded bg-white p-6 shadow">
                    <form method="POST" action="{{ route('admin.character-relationships.store') }}">
                        @csrf

                        @if (! empty($selectedWorkId))
                            <input type="hidden" name="return_to_work_id" value="{{ $selectedWorkId }}">
                        @endif

                        @include('admin.character_relationships._form', [
                            'works' => $works,
                            'characters' => $characters,
                            'characterRelationship' => null,
                            'selectedWorkId' => $selectedWorkId ?? null,
                        ])
                    </form>
                </div>
            @else
                <div class="rounded bg-yellow-50 p-6 text-yellow-800">
                    先に作品を選択してください。選択した作品に登録されているキャラクターだけが候補に表示されます。
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
