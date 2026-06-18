{{-- resources/views/partials/furigana-guide.blade.php --}}
{{-- Include this in question create/edit forms: @include('partials.furigana-guide') --}}

<div x-data="{ open: false }" class="rounded-lg border border-blue-200 bg-blue-50">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between px-4 py-3 text-left"
    >
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-semibold text-blue-800">
                ふりがな (Furigana) Guide — How to add reading above kanji
            </span>
        </div>
        <svg class="h-4 w-4 text-blue-600 transition-transform" :class="open ? 'rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak class="border-t border-blue-200 px-4 py-4">
        <!-- Syntax -->
        <div class="mb-4">
            <p class="mb-2 text-xs font-bold uppercase text-blue-700">Syntax</p>
            <div class="rounded-lg bg-white px-4 py-3 font-mono text-sm text-gray-800 border border-blue-100">
                {kanji|ふりがな}
            </div>
            <p class="mt-1 text-xs text-blue-600">
                Wrap kanji with <code class="bg-blue-100 px-1 rounded">{}</code>,
                separate kanji and reading with <code class="bg-blue-100 px-1 rounded">|</code>
            </p>
        </div>

        <!-- Examples -->
        <div class="mb-4">
            <p class="mb-2 text-xs font-bold uppercase text-blue-700">Examples</p>
            <div class="space-y-2">
                <div class="rounded-lg bg-white px-4 py-2 border border-blue-100">
                    <p class="font-mono text-sm text-gray-700">{社会福祉|しゃかいふくし}の理念を{発展|はってん}させた</p>
                    <p class="mt-1 text-xs text-gray-500">→ Furigana above 社会福祉 and 発展</p>
                </div>
                <div class="rounded-lg bg-white px-4 py-2 border border-blue-100">
                    <p class="font-mono text-sm text-gray-700">{日本語|にほんご}{能力|のうりょく}{試験|しけん}</p>
                    <p class="mt-1 text-xs text-gray-500">→ Furigana above each kanji word</p>
                </div>
                <div class="rounded-lg bg-white px-4 py-2 border border-blue-100">
                    <p class="font-mono text-sm text-gray-700">{人間|にんげん}の{尊厳|そんげん}と{自立|じりつ}</p>
                    <p class="mt-1 text-xs text-gray-500">→ Multiple furigana in one sentence</p>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div>
            <p class="mb-2 text-xs font-bold uppercase text-blue-700">Tips</p>
            <ul class="space-y-1 text-xs text-blue-700">
                <li class="flex items-start gap-1">
                    <span class="mt-0.5">✅</span>
                    <span>Plain text without <code class="bg-blue-100 px-1 rounded">{}</code> renders normally</span>
                </li>
                <li class="flex items-start gap-1">
                    <span class="mt-0.5">✅</span>
                    <span>Works in Question text, Options (A/B/C/D), and Explanation</span>
                </li>
                <li class="flex items-start gap-1">
                    <span class="mt-0.5">✅</span>
                    <span>Mix furigana and plain text freely in the same field</span>
                </li>
                <li class="flex items-start gap-1">
                    <span class="mt-0.5">⚠️</span>
                    <span>Don't nest <code class="bg-blue-100 px-1 rounded">{}</code> inside each other</span>
                </li>
            </ul>
        </div>

        <!-- Live Preview -->
        <div class="mt-4" x-data="{ preview: '{漢字|かんじ}の{読み方|よみかた}' }">
            <p class="mb-2 text-xs font-bold uppercase text-blue-700">Live Preview Test</p>
            <input
                type="text"
                x-model="preview"
                class="w-full rounded-lg border border-blue-200 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                placeholder="Type text with {kanji|furigana} here..."
            />
            <div class="mt-2 rounded-lg bg-white px-4 py-3 border border-blue-100 min-h-[40px]">
                <p class="text-xs text-gray-400 mb-1">Preview (approximate):</p>
                <div id="furigana-preview" class="text-sm text-gray-800"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple browser preview for furigana
document.addEventListener('alpine:initialized', () => {
    const updatePreview = () => {
        const inputs = document.querySelectorAll('[x-model="preview"]');
        inputs.forEach(input => {
            const preview = input.closest('[x-data]').querySelector('#furigana-preview');
            if (!preview) return;
            const text = input.value;
            const html = text.replace(/\{([^|{}]+)\|([^|{}]+)\}/g, (_, kanji, furigana) => {
                return `<ruby style="ruby-align:center"><rb>${kanji}</rb><rt style="font-size:0.6em;color:#6b7280;">${furigana}</rt></ruby>`;
            });
            preview.innerHTML = html;
        });
    };

    document.querySelectorAll('[x-model="preview"]').forEach(input => {
        input.addEventListener('input', updatePreview);
        updatePreview();
    });
});
</script>
