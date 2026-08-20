@extends('layouts.dashboard')

@php
    $isAbout = $page->slug === 'about-app';
    $isStudyLibrary = $page->slug === 'study-library';
    $pageTitle = match ($page->slug) {
        'about-app' => 'About App',
        'study-library' => 'Study Library',
        default => 'Privacy Policy',
    };
@endphp

@section('title', 'Edit ' . $pageTitle)
@section('page-title', 'Edit ' . $pageTitle)

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm" x-data="appPageForm({{ Js::from($page->items ?? []) }})">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">{{ $pageTitle }}</h3>
                <p class="mt-1 text-sm text-gray-600">This content is shown on the "{{ $pageTitle }}" screen in the app.</p>
            </div>

            <form action="{{ route('app-pages.update', $page->slug) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                @if ($isAbout)
                    <!-- Tagline -->
                    <div>
                        <label for="tagline" class="mb-2 block text-sm font-semibold text-gray-700">Tagline</label>
                        <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $page->tagline) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. Learn · Practice · Succeed">
                        @error('tagline')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- About paragraphs -->
                    <div>
                        <label for="intro_text_1" class="mb-2 block text-sm font-semibold text-gray-700">About Paragraph 1</label>
                        <textarea name="intro_text_1" id="intro_text_1" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('intro_text_1', $page->intro_text_1) }}</textarea>
                        @error('intro_text_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="intro_text_2" class="mb-2 block text-sm font-semibold text-gray-700">About Paragraph 2</label>
                        <textarea name="intro_text_2" id="intro_text_2" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('intro_text_2', $page->intro_text_2) }}</textarea>
                        @error('intro_text_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stat tiles -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Stat Tiles</label>
                        <div class="grid grid-cols-4 gap-3">
                            @for ($i = 1; $i <= 4; $i++)
                                <div>
                                    <input type="text" name="stat_{{ $i }}_value" value="{{ old('stat_' . $i . '_value', $page->{'stat_' . $i . '_value'}) }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                        placeholder="e.g. 10K+">
                                    @error('stat_' . $i . '_value')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Questions / Categories / Learners / Rating, in that order.</p>
                    </div>

                    <!-- Developer card -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="developer_name" class="mb-2 block text-sm font-semibold text-gray-700">Developer Name</label>
                            <input type="text" name="developer_name" id="developer_name" value="{{ old('developer_name', $page->developer_name) }}"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            @error('developer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="developer_role" class="mb-2 block text-sm font-semibold text-gray-700">Developer Role</label>
                            <input type="text" name="developer_role" id="developer_role" value="{{ old('developer_role', $page->developer_role) }}"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            @error('developer_role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="developer_url" class="mb-2 block text-sm font-semibold text-gray-700">Developer Link</label>
                            <input type="text" name="developer_url" id="developer_url" value="{{ old('developer_url', $page->developer_url) }}"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="https://...">
                            @error('developer_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Copyright -->
                    <div>
                        <label for="copyright_text" class="mb-2 block text-sm font-semibold text-gray-700">Copyright Text</label>
                        <input type="text" name="copyright_text" id="copyright_text" value="{{ old('copyright_text', $page->copyright_text) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        @error('copyright_text')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Features repeater -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Key Features</label>
                        <div class="space-y-3">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="flex gap-2 rounded-lg border border-gray-200 p-3">
                                    <button type="button" @click="togglePicker(i)" :title="item.icon"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center self-start rounded-lg border border-gray-300 bg-gray-50 transition hover:border-purple-500">
                                        <ion-icon :name="item.icon || 'star'" style="font-size: 22px; color: #7c3aed;"></ion-icon>
                                    </button>
                                    <div class="flex-1 space-y-2">
                                        <input type="text" :name="`items[${i}][title]`" x-model="item.title"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                            placeholder="Feature title">
                                        <textarea :name="`items[${i}][content]`" x-model="item.content" rows="2"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                            placeholder="Feature description"></textarea>
                                        <div x-show="openPicker === i" x-transition
                                            class="grid max-h-40 grid-cols-8 gap-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3">
                                            <template x-for="icon in icons" :key="icon">
                                                <button type="button" @click="selectIcon(i, icon)"
                                                    class="rounded-lg border p-2 transition hover:border-purple-500"
                                                    :class="item.icon === icon ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white'">
                                                    <ion-icon :name="icon" style="font-size: 18px;"
                                                        :style="`color: ${item.icon === icon ? '#7c3aed' : '#6b7280'}`"></ion-icon>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <input type="hidden" :name="`items[${i}][icon]`" :value="item.icon">
                                    <button type="button" @click="removeItem(i)" class="self-start p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addItem()"
                                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                                + Add Feature
                            </button>
                        </div>
                    </div>
                @elseif ($isStudyLibrary)
                    <!-- Tag -->
                    <div>
                        <label for="tagline" class="mb-2 block text-sm font-semibold text-gray-700">Tag</label>
                        <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $page->tagline) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 📖 STUDY LIBRARY">
                        @error('tagline')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="intro_text_1" class="mb-2 block text-sm font-semibold text-gray-700">Title</label>
                        <textarea name="intro_text_1" id="intro_text_1" rows="2"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. Explore 100+ Free Books">{{ old('intro_text_1', $page->intro_text_1) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Use \n for a line break, matching the app's title layout.</p>
                        @error('intro_text_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subtitle -->
                    <div>
                        <label for="intro_text_2" class="mb-2 block text-sm font-semibold text-gray-700">Subtitle</label>
                        <input type="text" name="intro_text_2" id="intro_text_2" value="{{ old('intro_text_2', $page->intro_text_2) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. Design, Dev, Business & more">
                        @error('intro_text_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Button Text -->
                    <div>
                        <label for="button_text" class="mb-2 block text-sm font-semibold text-gray-700">Button Text</label>
                        <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $page->button_text) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. Browse">
                        @error('button_text')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-xs text-gray-500">This card links to the Study tab in the app — the destination isn't editable here.</p>
                @else
                    <!-- Privacy intro -->
                    <div>
                        <label for="intro_text_1" class="mb-2 block text-sm font-semibold text-gray-700">Intro Text</label>
                        <textarea name="intro_text_1" id="intro_text_1" rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('intro_text_1', $page->intro_text_1) }}</textarea>
                        @error('intro_text_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last updated -->
                    <div>
                        <label for="last_updated_label" class="mb-2 block text-sm font-semibold text-gray-700">Last Updated Label</label>
                        <input type="text" name="last_updated_label" id="last_updated_label"
                            value="{{ old('last_updated_label', $page->last_updated_label) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. Last updated: January 2025">
                        @error('last_updated_label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sections repeater -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Policy Sections</label>
                        <div class="space-y-3">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="flex gap-2 rounded-lg border border-gray-200 p-3">
                                    <button type="button" @click="togglePicker(i)" :title="item.icon"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center self-start rounded-lg border border-gray-300 bg-gray-50 transition hover:border-purple-500">
                                        <ion-icon :name="item.icon || 'star'" style="font-size: 22px; color: #7c3aed;"></ion-icon>
                                    </button>
                                    <div class="flex-1 space-y-2">
                                        <input type="text" :name="`items[${i}][title]`" x-model="item.title"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                            placeholder="Section title">
                                        <textarea :name="`items[${i}][content]`" x-model="item.content" rows="4"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                            placeholder="Section content"></textarea>
                                        <div x-show="openPicker === i" x-transition
                                            class="grid max-h-40 grid-cols-8 gap-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3">
                                            <template x-for="icon in icons" :key="icon">
                                                <button type="button" @click="selectIcon(i, icon)"
                                                    class="rounded-lg border p-2 transition hover:border-purple-500"
                                                    :class="item.icon === icon ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white'">
                                                    <ion-icon :name="icon" style="font-size: 18px;"
                                                        :style="`color: ${item.icon === icon ? '#7c3aed' : '#6b7280'}`"></ion-icon>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <input type="hidden" :name="`items[${i}][icon]`" :value="item.icon">
                                    <button type="button" @click="removeItem(i)" class="self-start p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addItem()"
                                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                                + Add Section
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Save Changes
                    </button>
                    <a href="{{ route('dashboard') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Ionicons — same icon set used by the category icon picker -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script>
        function appPageForm(initialItems) {
            return {
                items: initialItems && initialItems.length ? initialItems : [{ title: '', content: '', icon: 'star' }],
                openPicker: null,
                icons: [
                    'color-palette', 'code-slash', 'briefcase', 'megaphone', 'camera',
                    'musical-notes', 'book', 'bulb', 'fitness', 'restaurant',
                    'airplane', 'car', 'bicycle', 'globe', 'home',
                    'heart', 'star', 'trophy', 'rocket', 'flag',
                    'game-controller', 'headset', 'mic', 'desktop', 'phone-portrait',
                    'cart', 'card', 'cash', 'calculator', 'pie-chart',
                    'bar-chart', 'stats-chart', 'trending-up', 'analytics', 'newspaper',
                    'people', 'person', 'school', 'library', 'flask',
                    'beaker', 'planet', 'leaf', 'cloud', 'thunderstorm',
                    'sunny', 'moon', 'basketball', 'football', 'baseball',
                    'hammer', 'construct', 'brush', 'color-fill', 'shapes'
                ],

                addItem() {
                    this.items.push({ title: '', content: '', icon: 'star' });
                },

                removeItem(i) {
                    this.items.splice(i, 1);
                    if (this.openPicker === i) this.openPicker = null;
                },

                togglePicker(i) {
                    this.openPicker = this.openPicker === i ? null : i;
                },

                selectIcon(i, icon) {
                    this.items[i].icon = icon;
                    this.openPicker = null;
                },
            }
        }
    </script>
@endpush
