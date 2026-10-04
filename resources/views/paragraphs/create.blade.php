@extends('layouts.dashboard')

@section('title', 'Create Paragraph')
@section('page-title', 'Create New Paragraph')

@section('content')
    <div class="max-w-4xl">
        <div class="mb-6">
            <a href="{{ route('paragraphs.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Paragraphs
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Paragraph Information</h3>
                <p class="mt-1 text-sm text-gray-600">Write the paragraph, then add its questions below with “Add Question”.</p>
            </div>

            <form action="{{ route('paragraphs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
                @csrf
                @include('paragraphs._form', ['paragraph' => null])

                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Save Paragraph &amp; Questions
                    </button>
                    <a href="{{ route('paragraphs.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
