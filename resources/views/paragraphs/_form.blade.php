{{-- Paragraph + its questions. $paragraph is null on create. --}}
@php
    $existing = $paragraph ? $paragraph->questions->keyBy('id') : collect();
    $optionImage = fn ($q, $i) => $q?->options->firstWhere('option_index', $i)?->image_url;

    if (is_array(old('questions'))) {
        // Re-show what was typed after a validation error (chosen files must be picked again).
        $rows = collect(old('questions'))->map(function ($r, $uid) use ($existing, $optionImage) {
            $q = !empty($r['id']) ? $existing->get((int) $r['id']) : null;
            return [
                'uid' => (string) $uid,
                'id' => $q?->id,
                'question' => $r['question'] ?? '',
                'image_url' => $q?->image_url,
                'options' => collect(range(0, 3))->map(fn ($i) => [
                    'text' => $r['options'][$i] ?? '',
                    'image_url' => $optionImage($q, $i),
                ])->all(),
                'correct_answer' => (string) ($r['correct_answer'] ?? ''),
                'difficulty' => $r['difficulty'] ?? 'Easy',
                'explanation' => $r['explanation'] ?? '',
            ];
        })->values();
    } else {
        $rows = $existing->values()->map(fn ($q) => [
            'uid' => 'q' . $q->id,
            'id' => $q->id,
            'question' => $q->question,
            'image_url' => $q->image_url,
            'options' => collect(range(0, 3))->map(fn ($i) => [
                'text' => $q->options->firstWhere('option_index', $i)?->option_text ?? '',
                'image_url' => $optionImage($q, $i),
            ])->all(),
            'correct_answer' => (string) $q->correct_answer,
            'difficulty' => $q->difficulty,
            'explanation' => $q->explanation,
        ]);
    }

    $questionErrors = collect($errors->getMessages())
        ->filter(fn ($m, $key) => str_starts_with($key, 'questions.'))
        ->map(fn ($m) => $m[0]);

    $selectedSets = $paragraph
        ? $paragraph->questions->flatMap->questionSets->pluck('id')->unique()->values()->all()
        : [];
    $position = $paragraph?->questions->first()?->position;
@endphp

@include('partials.furigana-guide')

@include('paragraphs._sets', ['selectedSets' => $selectedSets])

<!-- Position -->
<div>
    <label for="position" class="mb-2 block text-sm font-semibold text-gray-700">
        Position
        <span class="ml-1 font-normal text-gray-400">— order within question set (1–60)</span>
    </label>
    <input type="number" name="position" id="position" value="{{ old('position', $position) }}" min="1" max="60"
        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
        placeholder="e.g., 1, 2, 3 … 60  (leave empty to show last)">
    <p class="mt-1 text-xs text-gray-500">Where this paragraph (with all its questions) appears in the quiz.</p>
    @error('position')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<!-- Title -->
<div>
    <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
        Title <span class="font-normal text-gray-400">(optional)</span>
    </label>
    <input type="text" name="title" id="title" value="{{ old('title', $paragraph?->title) }}"
        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
        placeholder="e.g., Read the passage and answer the questions">
    @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<!-- Paragraph text -->
<div>
    <label for="content" class="mb-2 block text-sm font-semibold text-gray-700">
        Paragraph <span class="text-red-500">*</span>
    </label>
    <textarea name="content" id="content" rows="8" required
        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
        placeholder="Enter the paragraph text...">{{ old('content', $paragraph?->content) }}</textarea>
    @error('content')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@include('questions.partials.image-input', [
    'name' => 'image',
    'errorKey' => 'image',
    'label' => 'Paragraph Image',
    'current' => $paragraph?->image_url,
    'removeName' => $paragraph ? 'remove_image' : null,
])

<script>
    // Repeater for the paragraph's questions. Each row posts as questions[<uid>][...];
    // the uid stays fixed for a row so removing one never shifts the others' files.
    function paragraphQuestions(rows, errors) {
        let counter = 0;
        const blank = () => ({
            uid: 'n' + Date.now() + (counter++),
            id: null,
            question: '',
            image_url: null,
            options: [0, 1, 2, 3].map(() => ({ text: '', image_url: null })),
            correct_answer: '',
            difficulty: 'Easy',
            explanation: '',
        });
        return {
            rows: rows.length ? rows : [blank()],
            errors,
            add() {
                this.rows.push(blank());
                this.$nextTick(() => {
                    const cards = document.querySelectorAll('[data-question-card]');
                    cards[cards.length - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            remove(i) {
                if (this.rows.length > 1 && confirm('Remove this question?')) this.rows.splice(i, 1);
            },
            err(uid, field) {
                return this.errors[`questions.${uid}.${field}`] || '';
            },
            name(uid, field) {
                return `questions[${uid}]${field}`;
            },
        };
    }
</script>

<!-- Questions -->
<div x-data="paragraphQuestions(@js($rows), @js($questionErrors))" class="space-y-4">
    <div class="flex items-center justify-between border-t border-gray-200 pt-6">
        <div>
            <h4 class="text-lg font-bold text-gray-800">Questions <span class="text-red-500">*</span></h4>
            <p class="text-sm text-gray-600">Shown together with the paragraph on one page. Each question is worth 1 mark.</p>
        </div>
        <span class="rounded-full bg-purple-100 px-3 py-1 text-sm font-semibold text-purple-700"
            x-text="rows.length + (rows.length === 1 ? ' question' : ' questions')"></span>
    </div>
    @error('questions')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <template x-for="(row, i) in rows" :key="row.uid">
        <div data-question-card class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white"
                        x-text="i + 1"></span>
                    <span class="font-semibold text-gray-800">Question</span>
                </div>
                <button type="button" @click="remove(i)" x-show="rows.length > 1"
                    class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm text-red-600 transition hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>

            <template x-if="row.id">
                <input type="hidden" :name="name(row.uid, '[id]')" :value="row.id">
            </template>

            <!-- Question text -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Question <span class="text-red-500">*</span></label>
                <textarea rows="2" required :name="name(row.uid, '[question]')" x-model="row.question"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                    placeholder="Enter the question..."></textarea>
                <p class="mt-1 text-sm text-red-600" x-show="err(row.uid, 'question')" x-text="err(row.uid, 'question')"></p>
            </div>

            <!-- Question image -->
            <div x-data="{ preview: null, removeImg: false }">
                <label class="mb-1 block text-sm font-semibold text-gray-700">
                    Question Image <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <template x-if="row.image_url">
                    <div class="mb-2 flex items-center gap-3" x-show="!preview">
                        <img :src="row.image_url" alt="" class="h-20 rounded-lg border border-gray-200 object-contain"
                            :class="removeImg && 'opacity-30'">
                        <label class="flex items-center gap-2 text-sm text-red-600">
                            <input type="checkbox" value="1" :name="name(row.uid, '[remove_image]')" x-model="removeImg"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            Remove image
                        </label>
                    </div>
                </template>
                <div class="flex items-center gap-3">
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" :name="name(row.uid, '[image]')"
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
                    <img x-show="preview" :src="preview" alt="" class="h-16 rounded-lg border border-gray-200 object-contain">
                </div>
                <p class="mt-1 text-sm text-red-600" x-show="err(row.uid, 'image')" x-text="err(row.uid, 'image')"></p>
            </div>

            <!-- Options -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Answer Options <span class="text-red-500">*</span></label>
                <p class="mb-2 text-xs text-gray-500">Each option needs text, an image, or both.</p>
                <div class="space-y-3">
                    <template x-for="(opt, oi) in row.options" :key="oi">
                        <div>
                            <div class="flex items-start gap-3" x-data="{ preview: null, removeImg: false }">
                                <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-100 font-bold text-purple-700"
                                    x-text="String.fromCharCode(65 + oi)"></div>
                                <div class="flex-1 space-y-2">
                                    <input type="text" :name="name(row.uid, `[options][${oi}]`)" x-model="opt.text"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                        :placeholder="`Enter option ${String.fromCharCode(65 + oi)}`">
                                    <template x-if="opt.image_url">
                                        <div class="flex items-center gap-3" x-show="!preview">
                                            <img :src="opt.image_url" alt="" class="h-16 rounded-lg border border-gray-200 object-contain"
                                                :class="removeImg && 'opacity-30'">
                                            <label class="flex items-center gap-2 text-sm text-red-600">
                                                <input type="checkbox" value="1" x-model="removeImg"
                                                    :name="name(row.uid, `[remove_option_images][${oi}]`)"
                                                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                Remove image
                                            </label>
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-3">
                                        <input type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                                            :name="name(row.uid, `[option_images][${oi}]`)"
                                            @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
                                        <img x-show="preview" :src="preview" alt="" class="h-14 rounded-lg border border-gray-200 object-contain">
                                    </div>
                                </div>
                            </div>
                            <p class="ml-13 mt-1 text-sm text-red-600" x-show="err(row.uid, `options.${oi}`) || err(row.uid, `option_images.${oi}`)"
                                x-text="err(row.uid, `options.${oi}`) || err(row.uid, `option_images.${oi}`)"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Correct answer -->
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Correct Answer <span class="text-red-500">*</span></label>
                    <select required :name="name(row.uid, '[correct_answer]')" x-model="row.correct_answer"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Select correct answer</option>
                        <option value="0">Option A</option>
                        <option value="1">Option B</option>
                        <option value="2">Option C</option>
                        <option value="3">Option D</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600" x-show="err(row.uid, 'correct_answer')" x-text="err(row.uid, 'correct_answer')"></p>
                </div>

                <!-- Difficulty -->
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Difficulty Level <span class="text-red-500">*</span></label>
                    <select required :name="name(row.uid, '[difficulty]')" x-model="row.difficulty"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="Easy">😊 Easy</option>
                        <option value="Medium">😐 Medium</option>
                        <option value="Hard">😤 Hard</option>
                    </select>
                </div>
            </div>

            <!-- Explanation -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Explanation <span class="text-red-500">*</span></label>
                <textarea rows="2" required :name="name(row.uid, '[explanation]')" x-model="row.explanation"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                    placeholder="Explain why the correct answer is correct..."></textarea>
                <p class="mt-1 text-sm text-red-600" x-show="err(row.uid, 'explanation')" x-text="err(row.uid, 'explanation')"></p>
            </div>
        </div>
    </template>

    <button type="button" @click="add()"
        class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-purple-300 py-4 font-semibold text-purple-700 transition hover:border-purple-500 hover:bg-purple-50">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Question
    </button>
</div>
