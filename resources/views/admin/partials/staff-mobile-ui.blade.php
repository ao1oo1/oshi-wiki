@if (
    auth()->check()
    && auth()->user()?->isStaff()
    && ! auth()->user()?->canManageAllAdminFeatures()
    && request()->routeIs('admin.works.index', 'admin.characters.index', 'admin.character-relationships.index', 'admin.tags.index')
)
    {{-- STAFF_ADMIN_MOBILE_UI_TEMP_DISABLED_REACT185_FIX --}}
    <style>
        @media (max-width: 767px) {
            body {
                overflow-x: hidden;
            }

            main,
            .p-6 {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            .p-6 {
                padding: 16px !important;
            }

            .oshi-card {
                border-radius: 24px !important;
                padding: 20px !important;
                overflow: visible !important;
            }

            input[type="text"],
            input[type="search"],
            input[type="email"],
            input[type="password"],
            select,
            textarea {
                width: 100% !important;
                max-width: 100% !important;
            }

            .oshi-btn,
            button,
            a.oshi-btn {
                width: 100%;
                max-width: 100%;
                justify-content: center;
                text-align: center;
            }

            input[type="checkbox"] {
                width: 18px !important;
                height: 18px !important;
                min-width: 18px !important;
                min-height: 18px !important;
                border-radius: 3px !important;
                flex: 0 0 auto !important;
                -webkit-appearance: checkbox !important;
                appearance: checkbox !important;
            }

            .overflow-x-auto,
            .oshi-table-wrap {
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
            }

            .overflow-x-auto::-webkit-scrollbar,
            .oshi-table-wrap::-webkit-scrollbar {
                display: none !important;
            }
        }
    </style>
    {{-- /STAFF_ADMIN_MOBILE_UI_TEMP_DISABLED_REACT185_FIX --}}
@endif
