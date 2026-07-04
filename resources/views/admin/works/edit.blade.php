<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            作品編集
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-4xl">
            @include('admin.partials.navigation')

            <div class="mb-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.works.show', $work) }}"
                    style="display:inline-block;background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品詳細へ
                </a>

                <a
                    href="{{ route('admin.works.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品一覧へ
                </a>
            </div>

            <div class="rounded bg-white p-6 shadow">
                <form method="POST" action="{{ route('admin.works.update', $work) }}">
                    @csrf
                    @method('PUT')

                    @include('admin.works._form', [
                        'work' => $work,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
