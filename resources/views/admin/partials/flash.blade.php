@if (session('success'))
    <div class="mb-4 rounded-2xl border border-green-100 bg-green-50 px-5 py-4 text-green-800">
        <div class="font-bold">
            {{ session('success') }}
        </div>

        @if (auth()->user()?->isStaff())
            <div class="mt-3 text-sm font-bold leading-7">
                管理者が承認次第、情報が公開されます。今しばらくお待ちください。
            </div>
        @endif
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 font-bold text-red-700">
        {{ session('error') }}
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 font-bold text-blue-800">
        {{ session('status') }}
    </div>
@endif
