@if (
    auth()->check()
    && auth()->user()?->isStaff()
    && ! auth()->user()?->canManageAllAdminFeatures()
)
    {{-- STAFF_TAG_MOBILE_CARDS_BLADE_FIX --}}
    <style>
        @media (max-width: 767px) {
            .staff-tag-mobile-table-shell {
                display: none !important;
            }

            .staff-tag-mobile-card-list {
                display: grid;
                gap: 14px;
                margin-top: 18px;
            }

            .staff-tag-mobile-card {
                border: 1px solid #E2E8F0;
                border-radius: 22px;
                background: #FFFFFF;
                padding: 16px;
                box-shadow: 0 8px 20px rgba(45, 55, 72, 0.04);
            }

            .staff-tag-mobile-card-row {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 10px;
                align-items: start;
                padding: 9px 0;
                border-bottom: 1px solid #EDF2F7;
            }

            .staff-tag-mobile-card-row:last-child {
                border-bottom: 0;
            }

            .staff-tag-mobile-card-label {
                color: #A0AEC0;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.5;
            }

            .staff-tag-mobile-card-value {
                color: #2D3748;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.7;
                word-break: break-word;
            }
        }

        @media (min-width: 768px) {
            .staff-tag-mobile-card-list {
                display: none !important;
            }
        }
    </style>

    <div class="staff-tag-mobile-card-list">
        @forelse ($tags as $tag)
            <div class="staff-tag-mobile-card">
                <div class="staff-tag-mobile-card-row">
                    <div class="staff-tag-mobile-card-label">
                        タグ名
                    </div>
                    <div class="staff-tag-mobile-card-value">
                        {{ $tag->name }}
                    </div>
                </div>

                <div class="staff-tag-mobile-card-row">
                    <div class="staff-tag-mobile-card-label">
                        種類
                    </div>
                    <div class="staff-tag-mobile-card-value">
                        {{ $tag->type ?: '—' }}
                    </div>
                </div>

                <div class="staff-tag-mobile-card-row">
                    <div class="staff-tag-mobile-card-label">
                        説明
                    </div>
                    <div class="staff-tag-mobile-card-value">
                        {{ $tag->description ?: '—' }}
                    </div>
                </div>

                <div class="staff-tag-mobile-card-row">
                    <div class="staff-tag-mobile-card-label">
                        状態
                    </div>
                    <div class="staff-tag-mobile-card-value">
                        @include('admin.partials.status-badge', ['status' => $tag->status])
                    </div>
                </div>
            </div>
        @empty
            <div class="staff-tag-mobile-card">
                タグが登録されていません。
            </div>
        @endforelse
    </div>
    {{-- /STAFF_TAG_MOBILE_CARDS_BLADE_FIX --}}
@endif
