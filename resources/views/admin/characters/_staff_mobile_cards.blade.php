@if (
    auth()->check()
    && auth()->user()?->isStaff()
    && ! auth()->user()?->canManageAllAdminFeatures()
)
    {{-- STAFF_CHARACTER_MOBILE_CARDS_BLADE_FIX --}}
    <style>
        @media (max-width: 767px) {
            .staff-mobile-table-shell {
                display: none !important;
            }

            .staff-character-mobile-card-list {
                display: grid;
                gap: 14px;
                margin-top: 18px;
            }

            .staff-character-mobile-card {
                border: 1px solid #E2E8F0;
                border-radius: 22px;
                background: #FFFFFF;
                padding: 16px;
                box-shadow: 0 8px 20px rgba(45, 55, 72, 0.04);
            }

            .staff-character-mobile-card-row {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 10px;
                align-items: start;
                padding: 9px 0;
                border-bottom: 1px solid #EDF2F7;
            }

            .staff-character-mobile-card-row:last-child {
                border-bottom: 0;
            }

            .staff-character-mobile-card-label {
                color: #A0AEC0;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.5;
            }

            .staff-character-mobile-card-value {
                color: #2D3748;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.7;
                word-break: break-word;
            }

            .staff-character-mobile-card-actions {
                display: grid;
                gap: 10px;
                margin-top: 14px;
            }

            .staff-character-mobile-card-actions .oshi-btn,
            .staff-character-mobile-card-actions button,
            .staff-character-mobile-card-actions form {
                width: 100%;
                max-width: 100%;
            }

            .staff-character-mobile-card-actions .oshi-btn,
            .staff-character-mobile-card-actions button {
                justify-content: center;
                text-align: center;
            }
        }

        @media (min-width: 768px) {
            .staff-character-mobile-card-list {
                display: none !important;
            }
        }
    </style>

    <div class="staff-character-mobile-card-list">
        @forelse ($characters as $character)
            @php
                $currentUser = auth()->user();

                $canModifyCharacter = (bool) ($character->can_modify_by_current_user ?? false);

                if (! $canModifyCharacter && $currentUser && method_exists($currentUser, 'canModifyOwnedAdminContent')) {
                    $canModifyCharacter = $currentUser->canModifyOwnedAdminContent($character);
                }
            @endphp

            <div class="staff-character-mobile-card">
                <div class="staff-character-mobile-card-row">
                    <div class="staff-character-mobile-card-label">
                        キャラクター名
                    </div>
                    <div class="staff-character-mobile-card-value">
                        {{ $character->name }}
                    </div>
                </div>

                <div class="staff-character-mobile-card-row">
                    <div class="staff-character-mobile-card-label">
                        作品
                    </div>
                    <div class="staff-character-mobile-card-value">
                        {{ $character->work?->title ?? '—' }}
                    </div>
                </div>

                <div class="staff-character-mobile-card-row">
                    <div class="staff-character-mobile-card-label">
                        所属
                    </div>
                    <div class="staff-character-mobile-card-value">
                        {{ $character->affiliation ?: '—' }}
                    </div>
                </div>

                <div class="staff-character-mobile-card-row">
                    <div class="staff-character-mobile-card-label">
                        状態
                    </div>
                    <div class="staff-character-mobile-card-value">
                        @include('admin.partials.status-badge', ['status' => $character->status])
                    </div>
                </div>

                <div class="staff-character-mobile-card-row">
                    <div class="staff-character-mobile-card-label">
                        承認状態
                    </div>
                    <div class="staff-character-mobile-card-value">
                        {{ $character->review_status ?: '—' }}
                    </div>
                </div>

                <div class="staff-character-mobile-card-actions">
                    <a href="{{ route('admin.characters.show', $character) }}" class="oshi-btn oshi-btn-sub">
                        詳細
                    </a>

                    @if ($canModifyCharacter)
                        <a href="{{ route('admin.characters.edit', $character) }}" class="oshi-btn oshi-btn-sub">
                            編集
                        </a>

                        <form
                            method="POST"
                            action="{{ route('admin.characters.destroy', $character) }}"
                            onsubmit="return confirm('このキャラクターを削除します。よろしいですか？');"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="oshi-btn oshi-btn-sub text-red-600">
                                削除
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="staff-character-mobile-card">
                キャラクターが登録されていません。
            </div>
        @endforelse
    </div>
    {{-- /STAFF_CHARACTER_MOBILE_CARDS_BLADE_FIX --}}
@endif
