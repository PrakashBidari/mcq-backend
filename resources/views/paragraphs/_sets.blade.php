{{-- Question-set picker for a paragraph. $selectedSets - ids selected by default. --}}
<!-- Question Sets (Searchable Multi-Select) -->
<div x-data="{
    selectedSets: {{ json_encode(array_map('strval', old('question_sets', $selectedSets))) }},
    searchQuery: '',
    isOpen: false
}">
    <label class="mb-2 block text-sm font-semibold text-gray-700">
        Question Sets <span class="text-red-500">*</span>
    </label>

    <div class="mb-2 flex flex-wrap gap-2" x-show="selectedSets.length > 0">
        <template x-for="setId in selectedSets" :key="setId">
            <span
                class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-sm text-purple-700">
                <span x-text="document.querySelector(`[data-set-id='${setId}']`)?.dataset.setName"></span>
                <button type="button" @click="selectedSets = selectedSets.filter(id => id != setId)"
                    class="rounded-full p-0.5 hover:bg-purple-200">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        </template>
    </div>

    <div class="relative">
        <button type="button" @click="isOpen = !isOpen"
            class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-left focus:border-transparent focus:ring-2 focus:ring-purple-500">
            <span class="text-gray-700"
                x-text="selectedSets.length > 0 ? `${selectedSets.length} set(s) selected` : 'Select question sets...'"></span>
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="isOpen" @click.away="isOpen = false" x-cloak
            class="absolute z-10 mt-2 w-full rounded-lg border border-gray-300 bg-white shadow-lg">
            <div class="border-b border-gray-200 p-3">
                <input type="text" x-model="searchQuery" placeholder="Search question sets..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="max-h-64 overflow-y-auto">
                @foreach ($questionSets->groupBy('category.name') as $categoryName => $sets)
                    @php
                        $setNames = $sets->pluck('name')->map(fn($n) => strtolower($n))->toArray();
                        $setNamesJson = json_encode($setNames);
                    @endphp
                    <div
                        x-show="searchQuery === '' || '{{ strtolower($categoryName) }}'.includes(searchQuery.toLowerCase()) || {{ $setNamesJson }}.some(name => name.includes(searchQuery.toLowerCase()))">
                        <div class="sticky top-0 border-b border-purple-100 bg-purple-50 px-4 py-2">
                            <p class="text-xs font-bold uppercase text-purple-700">{{ $categoryName }}</p>
                        </div>
                        <div class="px-2 py-1">
                            @foreach ($sets as $set)
                                <label
                                    class="flex cursor-pointer items-center rounded px-3 py-2 hover:bg-gray-50"
                                    x-show="searchQuery === '' || '{{ strtolower($categoryName) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($set->name) }}'.includes(searchQuery.toLowerCase())"
                                    data-set-id="{{ $set->id }}" data-set-name="{{ $set->name }}">
                                    <input type="checkbox" name="question_sets[]"
                                        value="{{ $set->id }}" x-model="selectedSets"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-3 text-sm text-gray-700">{{ $set->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2 border-t border-gray-200 p-3">
                <button type="button" @click="selectedSets = {{ $questionSets->pluck('id')->toJson() }}"
                    class="flex-1 rounded bg-purple-100 px-3 py-2 text-sm text-purple-700 transition hover:bg-purple-200">
                    Select All
                </button>
                <button type="button" @click="selectedSets = []"
                    class="flex-1 rounded bg-gray-100 px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-200">
                    Clear All
                </button>
            </div>
        </div>
    </div>

    @error('question_sets')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-xs text-gray-500">All questions of this paragraph are added to these question sets</p>
</div>
