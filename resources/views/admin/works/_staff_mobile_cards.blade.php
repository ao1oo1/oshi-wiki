@if (
    auth()->check()
    && auth()->user()?->isStaff()
    && ! auth()->user()?->canManageAllAdminFeatures()
)
    {{-- STAFF_WORK_MOBILE_CARDS_BLADE_FIX_V2 --}}
    <style>
        @media (max-width: 767px) {
            /*
             * 作品管理ページでは、スタッフ表示時のみPC用テーブルを完全に隠す。
             * このpartialは作品管理ページだけにincludeするため、他画面には影響しない。
             */
            .oshi-table-wrap,
            .overflow-x-auto:has(table),
            .overflow-hidden:has(table),
            form:has(table),
            table {
                display: none !important;
            }

            .staff-work-mobile-card-list {
                display: grid !important;
                gap: 14px;
                margin-top: 18px;
            }

            .staff-work-mobile-card {
                display: block !important;
                border: 1px solid #E2E8F0;
                border-radius: 22px;
                background: #FFFFFF;
                padding: 16px;
                box-shadow: 0 8px 20px rgba(45, 55, 72, 0.04);
            }

            .staff-work-mobile-card-row {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 10px;
                align-items: start;
                padding: 9px 0;
                border-bottom: 1px solid #EDF2F7;
            }

            .staff-work-mobile-card-row:last-child {
                border-bottom: 0;
            }

            .staff-work-mobile-card-label {
                color: #A0AEC0;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.5;
            }

            .staff-work-mobile-card-value {
                color: #2D3748;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.7;
                word-break: break-word;
            }

            .staff-work-mobile-card-sub {
                margin-top: 2px;
                color: #A0AEC0;
                font-size: 12px;
                font-weight: 700;
            }

            .staff-work-mobile-card-actions {
                display: grid;
                gap: 10px;
                margin-top: 14px;
            }

            .staff-work-mobile-card-actions .oshi-btn,
            .staff-work-mobile-card-actions button,
            .staff-work-mobile-card-actions form {
                width: 100%;
                max-width: 100%;
            }

            .staff-work-mobile-card-actions .oshi-btn,
            .staff-work-mobile-card-actions button {
                justify-content: center;
                text-align: center;
            }
        }

        @media (min-width: 768px) {
            .staff-work-mobile-card-list {
                display: none !important;
            }
        }
    </style>

    <div class="staff-work-mobile-card-list">
        @forelse ($works as $work)
            @php
                $currentUser = auth()->user();

                $canModifyWork = false;

                if ($currentUser && method_exists($currentUser, 'canModifyOwnedAdminContent')) {
                    $canModifyWork = $currentUser->canModifyOwnedAdminContent($work);
                }
            @endphp

            <div class="staff-work-mobile-card">
                <div class="staff-work-mobile-card-row">
                    <div class="staff-work-mobile-card-label">
                        作品名
                    </div>
                    <div class="staff-work-mobile-card-value">
                        {{ $work->title }}

                        @if ($work->title_kana)
                            <div class="staff-work-mobile-card-sub">
                                {{ $work->title_kana }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="staff-work-mobile-card-row">
                    <div class="staff-work-mobile-card-label">
                        ジャンル
                    </div>
                    <div class="staff-work-mobile-card-value">
                        {{ $work->genre ?: '未設定' }}
                    </div>
                </div>

                <div class="staff-work-mobile-card-row">
                    <div class="staff-work-mobile-card-label">
                        原作媒体
                    </div>
                    <div class="staff-work-mobile-card-value">
                        {{ $work->original_media ?: '未設定' }}
                    </div>
                </div>

                <div class="staff-work-mobile-card-row">
                    <div class="staff-work-mobile-card-label">
                        タグ
                    </div>
                    <div class="staff-work-mobile-card-value">
                        @if ($work->tags->count())
                            @foreach ($work->tags as $tag)
                                <span class="oshi-chip">{{ $tag->name }}</span>
                            @endforeach
                        @else
                            <span class="oshi-muted">未設定</span>
                        @endif
                    </div>
                </div>

                <div class="staff-work-mobile-card-row">
                    <div class="staff-work-mobile-card-label">
                        状態
                    </div>
                    <div class="staff-work-mobile-card-value">
                        @include('admin.partials.status-badge', ['status' => $work->status])
                    </div>
                </div>

                <div class="staff-work-mobile-card-actions">
                    <a href="{{ route('admin.works.show', $work) }}" class="oshi-btn oshi-btn-sub">
                        詳細
                    </a>

                    @if ($canModifyWork)
                        <a href="{{ route('admin.works.edit', $work) }}" class="oshi-btn oshi-btn-sub">
                            編集
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="staff-work-mobile-card">
                作品はまだ登録されていません。
            </div>
        @endforelse
    </div>
    {{-- /STAFF_WORK_MOBILE_CARDS_BLADE_FIX_V2 --}}
@endif
