@extends('layouts.dashboard')

@section('title', 'Edit Book')
@section('page-title', 'Edit Book')

@section('content')
    <div class="max-w-3xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('books.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Books
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Book</h3>
                <p class="mt-1 text-sm text-gray-600">Update book information</p>
            </div>

            <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
                        Book Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="The Design of Everyday Things">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Author -->
                <div>
                    <label for="author" class="mb-2 block text-sm font-semibold text-gray-700">
                        Author <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Don Norman">
                    @error('author')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description (CKEditor) -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="This comprehensive guide covers the fundamental principles of design thinking and user-centered design...">{{ old('description', $book->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="description-error" class="mt-1 hidden text-sm text-red-600">Description cannot be empty if you
                        want to add content</p>
                    <p class="mt-1 text-xs text-gray-500">Brief description about the book (optional)</p>
                </div>

                <!-- Cover URL -->
                <div>
                    <label for="cover" class="mb-2 block text-sm font-semibold text-gray-700">
                        Cover Image URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" name="cover" id="cover" value="{{ old('cover', $book->cover) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="https://images.unsplash.com/photo-xxxxx">
                    @error('cover')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload Image (Optional) -->
                <div>
                    <label for="image" class="mb-2 block text-sm font-semibold text-gray-700">
                        Or Upload New Book Image (Optional)
                    </label>

                    @if ($book->image)
                        <div class="mb-3">
                            <p class="mb-2 text-sm text-gray-600">Current uploaded image:</p>
                            <img src="{{ asset('storage/' . $book->image) }}" alt="Book Image"
                                class="h-48 w-32 rounded-lg object-cover shadow-sm">
                        </div>
                    @endif

                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Upload a new image to replace the current one (JPG, PNG, GIF, WebP
                        - max 2MB)</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="mb-2 block text-sm font-semibold text-gray-700">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Difficulty -->
                    <div>
                        <label for="difficulty" class="mb-2 block text-sm font-semibold text-gray-700">
                            Difficulty <span class="text-red-500">*</span>
                        </label>
                        <select name="difficulty" id="difficulty" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Difficulty</option>
                            <option value="Beginner"
                                {{ old('difficulty', $book->difficulty) == 'Beginner' ? 'selected' : '' }}>Beginner
                            </option>
                            <option value="Intermediate"
                                {{ old('difficulty', $book->difficulty) == 'Intermediate' ? 'selected' : '' }}>Intermediate
                            </option>
                            <option value="Advanced"
                                {{ old('difficulty', $book->difficulty) == 'Advanced' ? 'selected' : '' }}>Advanced
                            </option>
                        </select>
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Rating -->
                    <div>
                        <label for="rating" class="mb-2 block text-sm font-semibold text-gray-700">
                            Rating (0-5) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="rating" id="rating" value="{{ old('rating', $book->rating) }}"
                            step="0.1" min="0" max="5" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="4.8">
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pages -->
                    <div>
                        <label for="pages" class="mb-2 block text-sm font-semibold text-gray-700">
                            Total Pages <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="pages" id="pages" value="{{ old('pages', $book->pages) }}"
                            min="1" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="368">
                        @error('pages')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Duration -->
                    <div>
                        <label for="duration" class="mb-2 block text-sm font-semibold text-gray-700">
                            Duration <span class="text-red-500">*</span>
                        </label>
                        <input type="time" min="1" name="duration" id="duration"
                            value="{{ old('duration', $book->duration) }}" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="12h 30m">
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Format: "12h 30m"</p>
                    </div>

                    <!-- Students -->
                    <div>
                        <label for="students" class="mb-2 block text-sm font-semibold text-gray-700">
                            Number of Students <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="students" id="students"
                            value="{{ old('students', $book->students) }}" min="0" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="12400">
                        @error('students')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active"
                        {{ old('is_active', $book->is_active) ? 'checked' : '' }}
                        class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">
                        Active (visible in mobile app)
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Update Book
                    </button>
                    <a href="{{ route('books.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        let descriptionEditor;

        ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote',
                    'insertTable', '|', 'undo', 'redo'
                ],
                heading: {
                    options: [{
                            model: 'paragraph',
                            title: 'Paragraph',
                            class: 'ck-heading_paragraph'
                        },
                        {
                            model: 'heading1',
                            view: 'h1',
                            title: 'Heading 1',
                            class: 'ck-heading_heading1'
                        },
                        {
                            model: 'heading2',
                            view: 'h2',
                            title: 'Heading 2',
                            class: 'ck-heading_heading2'
                        },
                        {
                            model: 'heading3',
                            view: 'h3',
                            title: 'Heading 3',
                            class: 'ck-heading_heading3'
                        }
                    ]
                }
            })
            .then(newEditor => {
                descriptionEditor = newEditor;

                // Update textarea before form submit
                document.querySelector('form').addEventListener('submit', function() {
                    document.getElementById('description').value = descriptionEditor.getData();
                });
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
