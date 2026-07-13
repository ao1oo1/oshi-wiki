@if (auth()->user()?->isStaff())
    <div class="mb-4 rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 text-sm font-bold text-blue-800">
        管理者が承認次第、情報が公開されます。今しばらくお待ちください。
    </div>
@endif
