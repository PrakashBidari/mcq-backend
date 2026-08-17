@extends('layouts.dashboard')

@section('title', 'Blog Categories')
@section('page-title', 'Blog Categories')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Blog Categories</h3>
                <p class="text-sm text-gray-600 mt-1">Categories used to organize blog posts</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'BlogCategory'))
                <a href="{{ route('blog-categories.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Add Blog Category
                </a>
            @endif
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Slug</th>
                        <th class="text-left py-2">Blogs</th>
                        <th class="text-center py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }};"></div>
                                    <span class="font-semibold text-gray-800">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="py-4"><code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $category->slug }}</code></td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">{{ $category->blogs_count }} blogs</span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'BlogCategory'))
                                        <a href="{{ route('blog-categories.edit', $category->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'BlogCategory'))
                                        <form action="{{ route('blog-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this blog category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-500">No blog categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
