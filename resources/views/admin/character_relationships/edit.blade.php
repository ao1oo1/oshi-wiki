<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            関係性編集
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-4xl">
            @include('admin.partials.navigation')


            <div class="mb-6">
                <a
                    href="{{ route('admin.character-relationships.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    関係性一覧へ
                </a>
            </div>

            <div class="rounded bg-white p-6 shadow">
                <form method="POST" action="{{ route('admin.character-relationships.update', $characterRelationship) }}">
                    @csrf
                    @method('PUT')

                    @include('admin.character_relationships._form', [
                        'works' => $works,
                        'characters' => $characters,
                        'characterRelationship' => $characterRelationship,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
