@extends('layouts.dashboard')

@section('title', 'Edit Blog')
@section('page-title', 'Edit Blog')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('blogs.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Blogs
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Blog</h3>
                <p class="mt-1 text-sm text-gray-600">Update blog post information</p>
            </div>

            <form action="{{ route('blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6 p-6" id="blogForm" onsubmit="return validateContent()">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
                        Blog Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $blog->title) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="10 Tips for Effective Learning">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div>
                    <label for="excerpt" class="mb-2 block text-sm font-semibold text-gray-700">
                        Excerpt <span class="text-red-500">*</span>
                    </label>
                    <textarea name="excerpt" id="excerpt" rows="3" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Brief summary of the blog post (shown in list view)">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    @error('excerpt')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">A short description (2-3 sentences)</p>
                </div>

                <!-- Content (CKEditor) -->
                <div>
                    <label for="content" class="mb-2 block text-sm font-semibold text-gray-700">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" id="content" rows="10"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="content-error" class="mt-1 hidden text-sm text-red-600">Content is required</p>
                </div>

                <!-- Cover URL -->
                <div>
                    <label for="cover_url" class="mb-2 block text-sm font-semibold text-gray-700">
                        Cover Image URL
                    </label>
                    <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url', $blog->cover_url) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="https://images.unsplash.com/photo-xxxxx">
                    @error('cover_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Enter a valid image URL (optional)</p>
                </div>

                <!-- Upload Image -->
                <div>
                    <label for="image" class="mb-2 block text-sm font-semibold text-gray-700">
                        Or Upload New Cover Image
                    </label>

                    @if ($blog->image)
                        <div class="mb-3">
                            <p class="mb-2 text-sm text-gray-600">Current uploaded image:</p>
                            <img src="{{ asset('storage/' . $blog->image) }}" alt="Blog Image"
                                class="h-32 w-48 rounded-lg object-cover shadow-sm">
                        </div>
                    @elseif($blog->cover_url)
                        <div class="mb-3">
                            <p class="mb-2 text-sm text-gray-600">Current cover image:</p>
                            <img src="{{ $blog->cover_url }}" alt="Blog Cover"
                                class="h-32 w-48 rounded-lg object-cover shadow-sm">
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
                        <label for="blog_category_id" class="mb-2 block text-sm font-semibold text-gray-700">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="blog_category_id" id="blog_category_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="">Select a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('blog_category_id', $blog->blog_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Manage categories under Blog &rarr; Blog Categories.</p>
                        @error('blog_category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Author -->
                    <div>
                        <label for="author" class="mb-2 block text-sm font-semibold text-gray-700">
                            Author <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="author" id="author" value="{{ old('author', $blog->author) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="John Doe">
                        @error('author')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Read Time -->
                <div>
                    <label for="read_time" class="mb-2 block text-sm font-semibold text-gray-700">
                        Read Time <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="read_time" id="read_time"
                        value="{{ old('read_time', $blog->read_time) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="5 min">
                    @error('read_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Format: "5 min" or "10 min read"</p>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active"
                        {{ old('is_active', $blog->is_active) ? 'checked' : '' }}
                        class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">
                        Active (visible in mobile app)
                    </label>
                </div>

                <!-- Stats Info -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="mb-1 text-xs font-semibold text-blue-600">Likes</p>
                            <p class="text-sm font-bold text-blue-800">{{ $blog->likes }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-semibold text-blue-600">Views</p>
                            <p class="text-sm font-bold text-blue-800">{{ number_format($blog->views) }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-semibold text-blue-600">Published</p>
                            <p class="text-sm font-bold text-blue-800">
                                {{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Draft' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Update Blog
                    </button>
                    <a href="{{ route('blogs.index') }}"
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
        let editor;

        ClassicEditor
            .create(document.querySelector('#content'), {
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
                editor = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        // Validate content before submit
        function validateContent() {
            const contentData = editor.getData();
            const contentError = document.getElementById('content-error');

            if (!contentData || contentData.trim() === '') {
                contentError.classList.remove('hidden');
                // Scroll to error
                document.getElementById('content').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }

            contentError.classList.add('hidden');
            // Update textarea value before submit
            document.getElementById('content').value = contentData;
            return true;
        }
    </script>
@endpush
