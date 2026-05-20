@extends('layouts.dashboard')

@section('title', 'Create Question')
@section('page-title', 'Create New Question')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('questions.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Questions
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Question Information</h3>
                <p class="mt-1 text-sm text-gray-600">Fill in all details to create a new question</p>
            </div>

            <form action="{{ route('questions.store') }}" method="POST" class="space-y-6 p-6">
                @csrf

                <!-- Question Sets (Searchable Multi-Select) -->
                <div x-data="{
                    selectedSets: {{ json_encode(old('question_sets', [])) }},
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
                    <p class="mt-1 text-xs text-gray-500">Search and select one or more question sets</p>
                </div>

                <!-- ── Position ── -->
                <div>
                    <label for="position" class="mb-2 block text-sm font-semibold text-gray-700">
                        Position
                        <span class="ml-1 font-normal text-gray-400">— order within question set (1–60)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </span>
                        <input type="number" name="position" id="position" value="{{ old('position') }}" min="1"
                            max="60"
                            class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g., 1, 2, 3 … 60  (leave empty to show last)">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Questions are displayed in ascending order (1 first). If two questions share a position, the latest
                        one appears first. Leave empty to show at the end.
                    </p>
                    @error('position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Question Text -->
                <div>
                    <label for="question" class="mb-2 block text-sm font-semibold text-gray-700">
                        Question <span class="text-red-500">*</span>
                    </label>
                    <textarea name="question" id="question" rows="3" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Enter your question here...">{{ old('question') }}</textarea>
                    @error('question')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Options -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Answer Options <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 font-bold text-purple-700">
                                    {{ chr(65 + $i) }}
                                </div>
                                <input type="text" name="options[]" value="{{ old('options.' . $i) }}" required
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                    placeholder="Enter option {{ chr(65 + $i) }}">
                            </div>
                            @error('options.' . $i)
                                <p class="ml-13 mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endfor
                    </div>
                </div>

                <!-- Correct Answer -->
                <div>
                    <label for="correct_answer" class="mb-2 block text-sm font-semibold text-gray-700">
                        Correct Answer <span class="text-red-500">*</span>
                    </label>
                    <select name="correct_answer" id="correct_answer" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Select correct answer</option>
                        @for ($i = 0; $i < 4; $i++)
                            <option value="{{ $i }}" {{ old('correct_answer') == $i ? 'selected' : '' }}>
                                Option {{ chr(65 + $i) }}
                            </option>
                        @endfor
                    </select>
                    @error('correct_answer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Difficulty -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Difficulty Level <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="difficulty" value="Easy"
                                {{ old('difficulty', 'Easy') == 'Easy' ? 'checked' : '' }} class="peer sr-only">
                            <div
                                class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-gray-200 p-4 transition hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-green-50">
                                <span class="font-semibold text-green-700">😊 Easy</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="difficulty" value="Medium"
                                {{ old('difficulty') == 'Medium' ? 'checked' : '' }} class="peer sr-only">
                            <div
                                class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-gray-200 p-4 transition hover:border-yellow-300 peer-checked:border-yellow-500 peer-checked:bg-yellow-50">
                                <span class="font-semibold text-yellow-700">😐 Medium</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="difficulty" value="Hard"
                                {{ old('difficulty') == 'Hard' ? 'checked' : '' }} class="peer sr-only">
                            <div
                                class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-gray-200 p-4 transition hover:border-red-300 peer-checked:border-red-500 peer-checked:bg-red-50">
                                <span class="font-semibold text-red-700">😤 Hard</span>
                            </div>
                        </label>
                    </div>
                    @error('difficulty')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Explanation -->
                <div>
                    <label for="explanation" class="mb-2 block text-sm font-semibold text-gray-700">
                        Explanation <span class="text-red-500">*</span>
                    </label>
                    <textarea name="explanation" id="explanation" rows="4" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Explain why the correct answer is correct...">{{ old('explanation') }}</textarea>
                    @error('explanation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Create Question
                    </button>
                    <a href="{{ route('questions.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
