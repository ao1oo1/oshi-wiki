<x-app-layout>

<x-slot name="header">
<h2 class="text-xl font-semibold">
作品登録
</h2>
</x-slot>

<div class="p-8">

<form
method="POST"
action="{{ route('admin.works.store') }}"
>

@csrf

<div>

<label>タイトル</label>

<input
type="text"
name="title"
class="border rounded w-full"
/>

</div>

<div class="mt-4">

<label>説明</label>

<textarea
name="description"
class="border rounded w-full"
></textarea>

</div>

<div class="mt-4">

<label>状態</label>

<select
name="status"
class="border rounded"
>

<option value="draft">下書き</option>
<option value="published">公開</option>
<option value="private">非公開</option>

</select>

</div>

<button
class="mt-6 bg-blue-600 text-white px-6 py-2 rounded"
>

登録

</button>

</form>

</div>

</x-app-layout>
