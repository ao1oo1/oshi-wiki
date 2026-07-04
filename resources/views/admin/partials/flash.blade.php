@if (session('success'))
    <div class="mb-4 rounded bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded bg-red-50 px-4 py-3 text-red-800">
        <p class="font-bold">入力内容を確認してください。</p>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
