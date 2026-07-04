<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            情報入力スタッフ申請
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">
                    情報入力スタッフ申請
                </h1>
                <p class="oshi-muted">
                    最高管理者のみ確認できます。公開リンクはメニューには掲載していません。
                </p>
            </div>

            <div class="oshi-table-wrap">
                <table class="oshi-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>ユーザーネーム</th>
                            <th>メールアドレス</th>
                            <th>Discord ID</th>
                            <th>申請日</th>
                            <th>登用開始日</th>
                            <th>登録作品件数</th>
                            <th>登録キャラクター件数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td>
                                    @if ($application->status === 'active')
                                        <span class="oshi-chip">登用中</span>
                                    @elseif ($application->status === 'rejected')
                                        <span class="oshi-badge">見送り</span>
                                    @else
                                        <span class="oshi-badge">申請中</span>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $application->username }}</strong>
                                </td>

                                <td>
                                    {{ $application->email }}
                                </td>

                                <td>
                                    {{ $application->discord_id ?: '未入力' }}
                                </td>

                                <td>
                                    {{ $application->applied_at?->format('Y/m/d H:i') ?: '未設定' }}
                                </td>

                                <td>
                                    {{ $application->started_at?->format('Y/m/d H:i') ?: '未設定' }}
                                </td>

                                <td>
                                    {{ $application->registered_works_count }}
                                </td>

                                <td>
                                    {{ $application->registered_characters_count }}
                                </td>

                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.contributor-applications.activate', $application) }}">
                                            @csrf
                                            <button type="submit" class="oshi-btn">
                                                登用開始
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.contributor-applications.reject', $application) }}">
                                            @csrf
                                            <button type="submit" class="oshi-btn oshi-btn-sub">
                                                見送り
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.contributor-applications.destroy', $application) }}" onsubmit="return confirm('この申請に削除フラグを付けます。よろしいですか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="oshi-btn oshi-btn-sub">
                                                削除フラグ
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="oshi-empty">
                                        申請はまだありません。
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
