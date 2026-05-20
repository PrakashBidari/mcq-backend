@extends('layouts.dashboard')

@section('title', 'View Message')
@section('page-title', 'Message Details')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('contact-messages.index') }}"
                class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Messages
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            <!-- Message Details -->
            <div class="lg:col-span-2">
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">{{ $message->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $message->email }}</p>
                            </div>
                            @if ($message->status === 'unread')
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                    Unread
                                </span>
                            @elseif($message->status === 'read')
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                    Read
                                </span>
                            @else
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Replied
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        <h4 class="mb-3 text-sm font-semibold text-gray-500">MESSAGE</h4>
                        <p class="whitespace-pre-wrap text-gray-700">{{ $message->message }}</p>
                    </div>

                    @if ($message->admin_reply)
                        <div class="border-t border-gray-200 bg-green-50 p-6">
                            <h4 class="mb-3 text-sm font-semibold text-green-700">YOUR REPLY</h4>
                            <p class="whitespace-pre-wrap text-gray-700">{{ $message->admin_reply }}</p>
                            <p class="mt-3 text-xs text-gray-500">Replied on
                                {{ $message->replied_at->format('M d, Y h:i A') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Reply Form -->
                @if ($message->status !== 'replied')
                    <div class="mt-6 rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800">Send Reply</h3>
                        </div>

                        <form action="{{ route('contact-messages.reply', $message->id) }}" method="POST" class="p-6">
                            @csrf

                            <div class="mb-4">
                                <label for="admin_reply" class="mb-2 block text-sm font-semibold text-gray-700">
                                    Your Reply <span class="text-red-500">*</span>
                                </label>
                                <textarea name="admin_reply" id="admin_reply" rows="6" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                    placeholder="Type your reply here..."></textarea>
                                @error('admin_reply')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6 rounded-lg border border-purple-200 bg-purple-50 p-4">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="send_email" id="send_email" value="1" checked
                                        class="mt-1 h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <div>
                                        <label for="send_email" class="font-semibold text-gray-800">
                                            Send Email Notification
                                        </label>
                                        <p class="text-sm text-gray-600">
                                            An email with your reply will be sent to <strong>{{ $message->email }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                                Send Reply
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Message Info -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h4 class="mb-4 font-bold text-gray-800">Message Info</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">Received</p>
                            <p class="text-sm text-gray-800">{{ $message->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $message->created_at->format('h:i A') }}</p>
                        </div>

                        @if ($message->read_at)
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Read At</p>
                                <p class="text-sm text-gray-800">{{ $message->read_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif

                        @if ($message->replied_at)
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Replied At</p>
                                <p class="text-sm text-gray-800">{{ $message->replied_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h4 class="mb-4 font-bold text-gray-800">Actions</h4>
                    <div class="space-y-2">
                        @if ($message->status === 'read')
                            <form action="{{ route('contact-messages.mark-unread', $message->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                                    Mark as Unread
                                </button>
                            </form>
                        @endif

                        @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'ContactMessage'))
                            <form action="{{ route('contact-messages.destroy', $message->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full rounded-lg bg-red-100 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-200">
                                    Delete Message
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
