<footer class="mt-12 border-t border-[#E2E8F0] bg-white">
    <div class="mx-auto max-w-6xl px-5 py-8">
        <nav class="flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm font-bold text-[#4A5568]">
            <a href="{{ route('public.pricing') }}" class="hover:underline">料金プラン</a>
            <a href="{{ route('public.privacy') }}" class="hover:underline">プライバシーポリシー</a>
            <a href="{{ route('public.terms') }}" class="hover:underline">利用規約</a>
            <a href="{{ route('public.legal') }}" class="hover:underline">特定商取引法に基づく表記</a>
            <a href="{{ route('public.billing-policy') }}" class="hover:underline">解約・返金ポリシー</a>
        </nav>
        <p class="mt-5 text-center text-xs text-[#718096]">
            © {{ date('Y') }} Oshi-Wiki
        </p>
    </div>
</footer>
