@extends('layouts.dashboard')

@section('title', 'Chat')
@section('page-title', 'Chat')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<style>
    .chat-sidebar { height: calc(100vh - 4rem - 3rem); }
    .chat-messages { height: calc(100vh - 4rem - 3rem - 73px - 69px); }
    .message-bubble-out { background: linear-gradient(135deg, #7c3aed, #9333ea); }
    .msg-text { font-size: 15px; line-height: 1.5; }
    .media-tab.active { background: #7c3aed; color: white; border-color: #7c3aed; box-shadow: 0 2px 6px rgba(124,58,237,.35); }
    .photo-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; }
    .photo-grid img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: pointer; transition: opacity .15s; }
    .photo-grid img:hover { opacity: .85; }
    .file-list { display: flex; flex-direction: column; gap: 8px; }
    .user-item.active {
        background: linear-gradient(135deg, #ede9fe, #f3e8ff);
        border-right: 3px solid #7c3aed;
    }
    .user-item:not(.active):hover { background-color: #f5f3ff; }
    .online-dot {
        width: 10px; height: 10px; background: #22c55e;
        border-radius: 50%; border: 2px solid white;
        position: absolute; bottom: 0; right: 0;
    }
    .msg-wrapper { position: relative; }
    .msg-dots-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 50%;
        color: #9ca3af; cursor: pointer;
        transition: background 0.15s, color 0.15s;
        flex-shrink: 0;
    }
    .msg-dots-btn:hover { background: #f3f4f6; color: #6b7280; }
    .msg-dropdown {
        display: none; position: absolute; z-index: 50;
        left: 50%; transform: translateX(-50%);
        bottom: calc(100% + 6px);
        background: #1f2937; border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        white-space: nowrap; overflow: hidden;
    }
    .msg-dropdown.open { display: block; }
    /* downward arrow pointing at the ... button */
    .msg-dropdown::after {
        content: '';
        position: absolute; bottom: -5px; left: 50%;
        transform: translateX(-50%);
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #1f2937;
    }
    #message-input { outline: none; box-shadow: none; }
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-track { background: #f1f5f9; }
    .chat-messages::-webkit-scrollbar-thumb { background: #c4b5fd; border-radius: 4px; }
    .sidebar-search { outline: none; box-shadow: none; }
    .unread-badge { display: none; }
    .unread-badge.show { display: flex; }

    /* Emoji Picker */
    #emoji-picker {
        position: absolute; bottom: calc(100% + 8px); left: 0;
        width: 320px; background: #fff;
        border: 1px solid #e5e7eb; border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        z-index: 100; overflow: hidden;
    }
    #emoji-picker.hidden { display: none; }
    #emoji-tabs { overflow-x: auto; scrollbar-width: none; flex-wrap: nowrap; }
    #emoji-tabs::-webkit-scrollbar { display: none; }
    .emoji-tab { cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 18px; transition: background .15s; flex-shrink: 0; }
    .emoji-tab:hover, .emoji-tab.active { background: #f3e8ff; }
    .emoji-btn { font-size: 20px; padding: 4px; border-radius: 6px; cursor: pointer; transition: background .15s; line-height: 1; }
    .emoji-btn:hover { background: #f3f4f6; transform: scale(1.2); }
    .emoji-input-wrap { position: relative; }
</style>
@endpush

@section('content')
<div class="flex h-full gap-0 overflow-hidden rounded-2xl shadow-lg border border-gray-200 bg-white"
    style="height: calc(100vh - 4rem - 3rem);">

    {{-- ─── Left Panel: Chat Window ─── --}}
    <div class="flex flex-col bg-gray-50" style="width:65%;" id="chat-panel">

        @if($users->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center">
            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-purple-100 text-purple-400">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700">No conversations yet</h3>
            <p class="mt-1 text-sm text-gray-400">There are no other admins or teachers to chat with.</p>
        </div>
        @else

        {{-- Chat Header --}}
        <div class="border-b border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-3">

                {{-- Left: User info --}}
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-700 text-sm font-bold text-white" id="header-initials">
                            {{ strtoupper(substr($users->first()->name, 0, 1)) }}
                        </div>
                        <span class="online-dot"></span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800" id="header-name">{{ $users->first()->name }}</p>
                        <p class="text-xs text-green-500" id="header-status">
                            <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-green-400"></span>
                            Online · {{ ucfirst($users->first()->role) }}
                        </p>
                    </div>
                </div>

                {{-- Right: Media tabs + Live indicator --}}
                <div class="flex items-center gap-3">
                    {{-- Photo / File buttons --}}
                    <div class="flex gap-2">
                        <button id="tab-photo"
                            onclick="openMediaPanel('photo')"
                            class="media-tab flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-500 shadow-sm transition hover:border-purple-400 hover:bg-purple-50 hover:text-purple-600 active:scale-95">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Photo
                        </button>
                        <button id="tab-file"
                            onclick="openMediaPanel('file')"
                            class="media-tab flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-500 shadow-sm transition hover:border-purple-400 hover:bg-purple-50 hover:text-purple-600 active:scale-95">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            File
                        </button>
                    </div>

                    {{-- Live / WS status --}}
                    <span id="ws-status" class="flex items-center gap-1 text-xs text-gray-400">
                        <span id="ws-dot" class="h-2 w-2 rounded-full bg-gray-300"></span>
                        <span id="ws-label">Connecting…</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Media Panel (Photo / File gallery) --}}
        <div id="media-panel" class="hidden flex-col bg-white" style="height: calc(100vh - 4rem - 3rem - 61px);">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-3">
                <h3 id="media-panel-title" class="text-sm font-semibold text-gray-700">Photos</h3>
                <button onclick="closeMediaPanel()"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="media-grid" class="flex-1 overflow-y-auto p-4">
                <div class="flex items-center justify-center h-full text-gray-400 text-sm" id="media-empty">
                    No media found.
                </div>
            </div>
        </div>

        {{-- Messages Area --}}
        <div class="chat-messages flex-1 overflow-y-auto px-6 py-4 space-y-2" id="messages-area">
            <div class="flex items-center gap-3 my-2">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium" id="chat-date-label">Today</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div id="messages-list" class="space-y-4"></div>
            <div id="messages-loading" class="flex justify-center py-6 hidden">
                <svg class="h-6 w-6 animate-spin text-purple-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>
            <div id="messages-empty" class="flex flex-col items-center justify-center py-10 text-center hidden">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-purple-50 text-purple-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400">No messages yet. Say hello! 👋</p>
            </div>
        </div>

        {{-- Message Input --}}
        <div id="chat-input-bar" class="border-t border-gray-200 bg-white px-4 py-3">
            <div class="emoji-input-wrap">
                {{-- Emoji Picker Panel --}}
                <div id="emoji-picker" class="hidden">
                    {{-- Category Tabs --}}
                    <div class="flex gap-1 border-b border-gray-100 px-3 py-2" id="emoji-tabs"></div>
                    {{-- Search --}}
                    <div class="px-3 pt-2">
                        <input id="emoji-search" type="text" placeholder="Search emoji…"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 placeholder-gray-400 focus:border-purple-400 focus:outline-none" />
                    </div>
                    {{-- Grid --}}
                    <div id="emoji-grid"
                        class="grid grid-cols-8 gap-0.5 overflow-y-auto px-3 py-2"
                        style="max-height:180px;">
                    </div>
                </div>

                {{-- File preview bar --}}
                <div id="file-preview-bar" class="hidden mb-2 flex items-center gap-3 rounded-xl border border-purple-200 bg-purple-50 px-3 py-2">
                    <div id="file-preview-content" class="flex flex-1 items-center gap-2 min-w-0"></div>
                    <button onclick="clearAttachment()" class="flex-shrink-0 text-gray-400 hover:text-red-500 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Hidden file input --}}
                <input type="file" id="file-input" class="hidden"
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" />

                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 transition focus-within:border-purple-400 focus-within:bg-white focus-within:shadow-sm">
                    <button id="emoji-toggle-btn" type="button" class="flex-shrink-0 text-gray-400 transition hover:text-purple-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <textarea id="message-input" rows="1"
                        placeholder="Type a message…"
                        class="flex-1 resize-none bg-transparent text-sm text-gray-700 placeholder-gray-400 border-0 focus:ring-0 max-h-28"></textarea>
                    <button id="attach-btn" type="button" class="flex-shrink-0 text-gray-400 transition hover:text-purple-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </button>
                    <button id="send-btn"
                        class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-800 text-white shadow-sm transition hover:shadow-md active:scale-95">
                        <svg class="h-4 w-4 -rotate-90" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <p class="mt-1.5 text-center text-[10px] text-gray-300">
                <kbd class="rounded bg-gray-100 px-1 py-0.5 text-gray-400">Enter</kbd> to send ·
                <kbd class="rounded bg-gray-100 px-1 py-0.5 text-gray-400">Shift+Enter</kbd> for new line
            </p>
        </div>

        @endif
    </div>

    {{-- ─── Right Sidebar: User List ─── --}}
    <div class="flex flex-shrink-0 flex-col border-l border-gray-100 bg-white" style="width:35%;">

        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-base font-bold text-gray-800">Members</h2>
                <p class="text-xs text-gray-400">{{ $users->count() }} {{ Str::plural('member', $users->count()) }}</p>
            </div>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        <div class="px-4 py-3">
            <div class="flex items-center gap-2 rounded-xl bg-gray-50 border border-gray-100 px-3 py-2">
                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                </svg>
                <input id="search-users" type="text" placeholder="Search members…"
                    class="sidebar-search w-full bg-transparent text-sm text-gray-700 placeholder-gray-400 border-0" />
            </div>
        </div>

        <div class="chat-sidebar flex-1 overflow-y-auto px-2 pb-4">
            @php
                $admins   = $users->where('role', 'admin');
                $teachers = $users->where('role', 'teacher');
            @endphp

            @php $firstItem = true; @endphp

        @if($admins->isNotEmpty())
            <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Admins</p>
            @foreach($admins as $user)
            <div class="user-item {{ $firstItem ? 'active' : '' }} flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-150"
            @php $firstItem = false; @endphp
                data-user-id="{{ $user->id }}"
                data-user-name="{{ $user->name }}"
                data-user-role="Admin"
                data-user-initial="{{ strtoupper(substr($user->name, 0, 1)) }}">
                <div class="relative flex-shrink-0">
                    <div class="relative h-9 w-9">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-700 text-xs font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @if(!empty($user->profile_image))
                            <img src="{{ $user->profile_image }}" alt="{{ $user->name }}"
                                class="absolute inset-0 h-9 w-9 rounded-full object-cover ring-2 ring-purple-100"
                                onerror="this.remove()" />
                        @endif
                    </div>
                    <span class="online-dot"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">Admin</p>
                </div>
                <span class="unread-badge ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-purple-500 text-[9px] font-bold text-white" id="badge-{{ $user->id }}"></span>
            </div>
            @endforeach
            @endif

            @if($teachers->isNotEmpty())
            <p class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Teachers</p>
            @foreach($teachers as $user)
            <div class="user-item {{ $firstItem ? 'active' : '' }} flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-150"
            @php $firstItem = false; @endphp
                data-user-id="{{ $user->id }}"
                data-user-name="{{ $user->name }}"
                data-user-role="Teacher"
                data-user-initial="{{ strtoupper(substr($user->name, 0, 1)) }}">
                <div class="relative flex-shrink-0">
                    <div class="relative h-9 w-9">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-xs font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @if(!empty($user->profile_image))
                            <img src="{{ $user->profile_image }}" alt="{{ $user->name }}"
                                class="absolute inset-0 h-9 w-9 rounded-full object-cover ring-2 ring-purple-100"
                                onerror="this.remove()" />
                        @endif
                    </div>
                    <span class="online-dot"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">Teacher</p>
                </div>
                <span class="unread-badge ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-purple-500 text-[9px] font-bold text-white" id="badge-{{ $user->id }}"></span>
            </div>
            @endforeach
            @endif

            @if($users->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <p class="text-sm font-medium text-gray-500">No members found</p>
            </div>
            @endif
        </div>

        {{-- My Info --}}
        <div class="border-t border-gray-100 px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="relative flex-shrink-0">
                    <div class="relative h-9 w-9">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-800 text-sm font-bold text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        @if(!empty(auth()->user()->profile_image))
                            <img src="{{ auth()->user()->profile_image }}" alt="me"
                                class="absolute inset-0 h-9 w-9 rounded-full object-cover"
                                onerror="this.remove()" />
                        @endif
                    </div>
                    <span class="online-dot"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-purple-500">{{ ucfirst(auth()->user()->role) }} · You</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
{{-- Pusher JS (Reverb uses the Pusher protocol) --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const ME = {
    id:      {{ auth()->id() }},
    name:    @json(auth()->user()->name),
    initial: @json(strtoupper(substr(auth()->user()->name, 0, 1))),
};

const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const SEND_URL  = "{{ route('chat.send') }}";
const MSGS_BASE = "{{ url('dashboard/chat') }}";

let activeUserId   = null;
let activeUserName = null;
let activeUserRole = null;
// let activeMediaTab = null;

// ── Unread counts (in-memory) ──────────────────────────────────────────
const unreadCounts = {};

function setUnread(userId, count) {
    unreadCounts[userId] = count;
    const badge = document.getElementById('badge-' + userId);
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.add('show');
    } else {
        badge.classList.remove('show');
    }
}

// ── Fetch unread counts on load ────────────────────────────────────────
fetch("{{ route('chat.unread') }}", { headers: { 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json())
    .then(data => {
        for (const [userId, count] of Object.entries(data)) {
            setUnread(parseInt(userId), count);
        }
    });

// ── Pusher / Reverb connection ─────────────────────────────────────────
const pusher = new Pusher("mcqapp-key", {
    wsHost:            "app.ikigaijobplacement.com",
    wsPort:            443,
    wssPort:           443,
    forceTLS:          true,
    enabledTransports: ['ws', 'wss'],
    cluster:           'mt1',
    authEndpoint:      '/broadcasting/auth',
    auth: { headers: { 'X-CSRF-TOKEN': CSRF } },
});

// ── Connection status indicator ────────────────────────────────────────
const wsDot   = document.getElementById('ws-dot');
const wsLabel = document.getElementById('ws-label');

pusher.connection.bind('connected', () => {
    wsDot.className   = 'h-2 w-2 rounded-full bg-green-400';
    wsLabel.textContent = 'Live';
});
pusher.connection.bind('connecting', () => {
    wsDot.className   = 'h-2 w-2 rounded-full bg-yellow-400';
    wsLabel.textContent = 'Connecting…';
});
pusher.connection.bind('disconnected', () => {
    wsDot.className   = 'h-2 w-2 rounded-full bg-red-400';
    wsLabel.textContent = 'Disconnected';
});
pusher.connection.bind('failed', () => {
    wsDot.className   = 'h-2 w-2 rounded-full bg-red-500';
    wsLabel.textContent = 'Failed — run: php artisan reverb:start';
});

const channel = pusher.subscribe('private-chat.' + ME.id);

channel.bind('message.sent', function (data) {
    if (data.sender_id === ME.id) return; // ignore my own messages
    if (data.sender_id === activeUserId) {
        // Active conversation — show message, no nav badge needed
        appendMessage(data.id, data.body, data.time, false, data.sender_id, false,
            data.attachment, data.attachment_name, data.attachment_type);
    } else {
        // Different conversation — increment sidebar unread + nav badge
        const cur = (unreadCounts[data.sender_id] || 0) + 1;
        setUnread(data.sender_id, cur);
        if (window.incrementChatNavBadge) window.incrementChatNavBadge();
    }
});

const DELETE_BASE  = "{{ url('dashboard/chat/message') }}";
const MEDIA_BASE   = "{{ url('dashboard/chat') }}";

channel.bind('message.deleted', function (data) {
    markDeleted(data.id);
});

// ── Load messages for a user ───────────────────────────────────────────
function loadMessages(userId) {
    const list    = document.getElementById('messages-list');
    const loading = document.getElementById('messages-loading');
    const empty   = document.getElementById('messages-empty');

    list.innerHTML = '';
    loading.classList.remove('hidden');
    empty.classList.add('hidden');

    fetch(MSGS_BASE + '/' + userId + '/messages', {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(messages => {
        loading.classList.add('hidden');
        if (messages.length === 0) {
            empty.classList.remove('hidden');
            return;
        }
        messages.forEach(m => appendMessage(m.id, m.body, m.time, m.mine, m.sender_id, m.deleted,
            m.attachment, m.attachment_name, m.attachment_type));
        scrollBottom();
        // clear the nav badge since we've now read this conversation
        if (window.clearChatNavBadge) window.clearChatNavBadge();
    })
    .catch(() => loading.classList.add('hidden'));
}

// ── Detect emoji-only body ─────────────────────────────────────────────
function isEmojiOnly(str) {
    if (!str || !str.trim()) return false;
    return !str.trim().replace(/[\p{Emoji_Presentation}\p{Extended_Pictographic}\s]/gu, '').length;
}

// ── Render body text with emojis enlarged ─────────────────────────────
function formatBody(str) {
    return escHtml(str).replace(
        /[\p{Emoji_Presentation}\p{Extended_Pictographic}]/gu,
        m => `<span style="font-size:1.2em;vertical-align:-0.1em;line-height:1">${m}</span>`
    );
}

// ── Attachment HTML helpers ────────────────────────────────────────────
function attachmentHtml(url, name, type) {
    if (!url) return '';
    if (type === 'image') {
        return `<div class="relative mt-2 inline-block">
            <a href="${url}" class="chat-img-link" data-title="${escHtml(name)}">
                <img src="${url}" alt="${escHtml(name)}"
                    class="max-w-[160px] rounded-xl border border-white/20 object-cover shadow-sm cursor-pointer hover:opacity-90 transition" />
            </a>
            <a href="${url}" download="${escHtml(name)}" title="Download"
                class="absolute bottom-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
            </a>
        </div>`;
    }
    // file
    const ext = (name || '').split('.').pop().toUpperCase();
    return `<a href="${url}" target="_blank" download="${escHtml(name)}"
        class="mt-2 flex items-center gap-2 rounded-xl border border-white/20 bg-white/15 px-3 py-2 text-xs text-white hover:bg-white/25 transition">
        <svg class="h-5 w-5 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <span class="truncate max-w-[140px] text-white">${escHtml(name)}</span>
        <span class="flex-shrink-0 rounded bg-white/25 px-1 py-0.5 text-[9px] font-bold text-white">${ext}</span>
    </a>`;
}

function attachmentHtmlIncoming(url, name, type) {
    if (!url) return '';
    if (type === 'image') {
        return `<div class="relative mt-2 inline-block">
            <a href="${url}" class="chat-img-link" data-title="${escHtml(name)}">
                <img src="${url}" alt="${escHtml(name)}"
                    class="max-w-[160px] rounded-xl border border-gray-100 object-cover shadow-sm cursor-pointer hover:opacity-90 transition" />
            </a>
            <a href="${url}" download="${escHtml(name)}" title="Download"
                class="absolute bottom-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
            </a>
        </div>`;
    }
    const ext = (name || '').split('.').pop().toUpperCase();
    return `<a href="${url}" target="_blank" download="${escHtml(name)}"
        class="mt-2 flex items-center gap-2 rounded-xl border border-purple-200 bg-purple-600 px-3 py-2 text-xs text-white hover:bg-purple-700 transition">
        <svg class="h-5 w-5 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <span class="truncate max-w-[140px] text-white">${escHtml(name)}</span>
        <span class="flex-shrink-0 rounded bg-white/25 px-1 py-0.5 text-[9px] font-bold text-white">${ext}</span>
    </a>`;
}

// ── Render a single message bubble ────────────────────────────────────
function appendMessage(id, body, time, mine, senderId, deleted = false,
    attachment = null, attachmentName = null, attachmentType = null) {
    const list = document.getElementById('messages-list');
    const empty = document.getElementById('messages-empty');
    empty.classList.add('hidden');

    const initial = mine
        ? ME.initial
        : (document.querySelector('[data-user-id="' + senderId + '"]')
              ?.dataset.userInitial || '?');

    const el = document.createElement('div');
    el.id = 'msg-' + id;

    if (deleted) {
        el.className = mine ? 'flex items-end justify-end gap-2' : 'flex items-end gap-2';
        el.innerHTML = deletedBubble(mine, initial, time);
    } else if (mine) {
        el.className = 'flex items-end justify-end gap-2';
        el.innerHTML = `
            <div class="msg-wrapper flex items-center gap-1">
                <div class="relative self-center">
                    <button class="msg-dots-btn text-gray-400 hover:text-gray-600"
                        onclick="toggleDropdown(this)" title="Options">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                        </svg>
                    </button>
                    <div class="msg-dropdown">
                        <button class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-400 hover:bg-white/10 transition"
                            onclick="deleteMessage(${id})">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
                <div class="max-w-xs lg:max-w-md text-right">
                    ${(attachmentType === 'image' && !body) || (!attachment && isEmojiOnly(body))
                        ? `<div class="inline-block">
                            ${attachmentHtml(attachment, attachmentName, attachmentType)}
                            ${body ? `<p class="text-lg leading-tight">${escHtml(body)}</p>` : ''}
                           </div>`
                        : `<div class="message-bubble-out inline-block rounded-2xl rounded-br-none px-4 py-1.5 shadow-sm">
                            ${body ? `<p class="msg-text text-white">${formatBody(body)}</p>` : ''}
                            ${attachmentHtml(attachment, attachmentName, attachmentType)}
                           </div>`
                    }
                    <p class="mt-1 text-[10px] text-gray-400 pr-1">${time} · ✓</p>
                </div>
            </div>
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-800 text-xs font-bold text-white">${escHtml(initial)}</div>`;
    } else {
        el.className = 'flex items-end gap-2';
        el.innerHTML = `
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-700 text-xs font-bold text-white">${escHtml(initial)}</div>
            <div class="max-w-xs lg:max-w-md">
                ${(attachmentType === 'image' && !body) || (!attachment && isEmojiOnly(body))
                    ? `<div>
                        ${attachmentHtmlIncoming(attachment, attachmentName, attachmentType)}
                        ${body ? `<p class="text-lg leading-tight">${escHtml(body)}</p>` : ''}
                       </div>`
                    : `<div class="rounded-2xl rounded-bl-none bg-white px-4 py-1.5 shadow-sm border border-gray-100">
                        ${body ? `<p class="msg-text text-gray-700">${formatBody(body)}</p>` : ''}
                        ${attachmentHtmlIncoming(attachment, attachmentName, attachmentType)}
                       </div>`
                }
                <p class="mt-1 text-[10px] text-gray-400 pl-1">${time}</p>
            </div>`;
    }

    list.appendChild(el);
    scrollBottom();
}

function deletedBubble(mine, initial, time) {
    if (mine) {
        return `
            <div class="max-w-xs lg:max-w-md text-right">
                <div class="inline-flex items-center gap-1.5 rounded-2xl rounded-br-none border border-gray-200 bg-gray-100 px-4 py-1.5">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <p class="text-xs italic text-gray-400">Message deleted</p>
                </div>
                <p class="mt-1 text-[10px] text-gray-300 pr-1">${time}</p>
            </div>
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-400">${escHtml(initial)}</div>`;
    } else {
        return `
            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-400">${escHtml(initial)}</div>
            <div class="max-w-xs lg:max-w-md">
                <div class="inline-flex items-center gap-1.5 rounded-2xl rounded-bl-none border border-gray-200 bg-gray-100 px-4 py-1.5">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <p class="text-xs italic text-gray-400">Message deleted</p>
                </div>
                <p class="mt-1 text-[10px] text-gray-300 pl-1">${time}</p>
            </div>`;
    }
}

// ── Replace a bubble with "Message deleted" placeholder ───────────────
function markDeleted(msgId) {
    const el = document.getElementById('msg-' + msgId);
    if (!el) return;
    const mine = el.classList.contains('justify-end');
    // guess initial from existing avatar
    const avatarEl = mine
        ? el.querySelector('div:last-child')
        : el.querySelector('div:first-child');
    const initial = avatarEl?.textContent?.trim() || '?';
    el.innerHTML = deletedBubble(mine, initial, '');
}

function scrollBottom() {
    const area = document.getElementById('messages-area');
    area.scrollTop = area.scrollHeight;
}

// ── Dropdown toggle ────────────────────────────────────────────────────
function toggleDropdown(btn) {
    const dropdown = btn.nextElementSibling;
    const isOpen   = dropdown.classList.contains('open');
    // close all open dropdowns first
    document.querySelectorAll('.msg-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dropdown.classList.add('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    if (!e.target.closest('.msg-wrapper')) {
        document.querySelectorAll('.msg-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});

// ── Chat image lightbox (event delegation for dynamic messages) ────────
document.addEventListener('click', function (e) {
    const link = e.target.closest('.chat-img-link');
    if (!link) return;
    e.preventDefault();

    const allLinks = [...document.querySelectorAll('#messages-area .chat-img-link')];
    const idx      = allLinks.indexOf(link);

    if (window._msgLightbox) window._msgLightbox.destroy();
    window._msgLightbox = GLightbox({
        elements: allLinks.map(a => ({
            href:        a.href,
            type:        'image',
            title:       a.dataset.title || '',
        })),
        startAt:          Math.max(idx, 0),
        touchNavigation:  true,
        loop:             true,
    });
    window._msgLightbox.open();
});

// ── Delete a message ───────────────────────────────────────────────────
function deleteMessage(msgId) {
    // close any open dropdown
    document.querySelectorAll('.msg-dropdown.open').forEach(d => d.classList.remove('open'));

    fetch(DELETE_BASE + '/' + msgId, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(() => markDeleted(msgId))
    .catch(() => alert('Could not delete message.'));
}

// ── Attachment state ──────────────────────────────────────────────────
let pendingFile = null;

document.getElementById('attach-btn')?.addEventListener('click', () => {
    document.getElementById('file-input').click();
});

document.getElementById('file-input')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    pendingFile = file;

    const isImage = file.type.startsWith('image/');
    const preview = document.getElementById('file-preview-content');
    const bar     = document.getElementById('file-preview-bar');

    if (isImage) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `
                <img src="${e.target.result}" class="h-12 w-12 rounded-lg object-cover flex-shrink-0 border border-purple-200" />
                <span class="truncate text-xs text-purple-700 font-medium">${escHtml(file.name)}</span>`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <svg class="h-6 w-6 flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="truncate text-xs text-purple-700 font-medium">${escHtml(file.name)}</span>`;
    }
    bar.classList.remove('hidden');
    this.value = '';
});

function clearAttachment() {
    pendingFile = null;
    document.getElementById('file-preview-bar').classList.add('hidden');
    document.getElementById('file-preview-content').innerHTML = '';
}

// ── Send a message ─────────────────────────────────────────────────────
function sendMessage() {
    const input = document.getElementById('message-input');
    const body  = input.value.trim();
    if (!body && !pendingFile) return;
    if (!activeUserId) return;

    const fileToSend = pendingFile;
    input.value = '';
    input.style.height = 'auto';
    clearAttachment();

    const fd = new FormData();
    fd.append('receiver_id', activeUserId);
    if (body) fd.append('body', body);
    if (fileToSend) fd.append('attachment', fileToSend);

    const headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
    if (pusher.connection.socket_id) headers['X-Socket-ID'] = pusher.connection.socket_id;

    fetch(SEND_URL, {
        method:  'POST',
        headers,
        body: fd,
    })
    .then(r => r.json())
    .then(msg => {
        appendMessage(msg.id, msg.body, msg.time, true, ME.id, false,
            msg.attachment, msg.attachment_name, msg.attachment_type);
    })
    .catch(() => {
        input.value = body;
    });
}

// ── Input auto-grow + Enter to send ───────────────────────────────────
const inputEl = document.getElementById('message-input');
if (inputEl) {
    inputEl.addEventListener('input', () => {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 112) + 'px';
    });
    inputEl.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
}
document.getElementById('send-btn')?.addEventListener('click', sendMessage);

// ── User item click ────────────────────────────────────────────────────
document.querySelectorAll('.user-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.user-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        closeMediaPanel();
        activeUserId   = parseInt(item.dataset.userId);
        activeUserName = item.dataset.userName;
        activeUserRole = item.dataset.userRole;

        document.getElementById('header-name').textContent = activeUserName;
        document.getElementById('header-status').innerHTML =
            `<span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-green-400"></span>Online · ${activeUserRole}`;
        document.getElementById('header-initials').textContent =
            item.dataset.userInitial;

        // Clear unread badge
        setUnread(activeUserId, 0);

        loadMessages(activeUserId);
    });
});

// ── Sidebar search ─────────────────────────────────────────────────────
document.getElementById('search-users')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.user-item').forEach(item => {
        item.style.display = item.dataset.userName.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Auto-select first user on load ────────────────────────────────────
const firstUser = document.querySelector('.user-item');
if (firstUser) firstUser.click();

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Emoji Picker ───────────────────────────────────────────────────────
const EMOJI_CATEGORIES = [
    { label: '😀', name: 'Smileys', emojis: ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕'] },
    { label: '👍', name: 'Gestures', emojis: ['👍','👎','👌','🤌','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','☝️','✋','🖐️','🖖','👋','🤚','💪','🙌','👏','🤲','🙏','✍️','💅','🤳','👀','💋','👄','🦷','👅','👂','👃','🧠','🦶','🦵'] },
    { label: '❤️', name: 'Hearts', emojis: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','❤️‍🔥','❤️‍🩹','🫀'] },
    { label: '🎉', name: 'Symbols', emojis: ['🎉','🎊','🎈','🎁','🏆','🥇','💯','✅','❌','⭐','🌟','💫','✨','🔥','⚡','💥','🌈','🎯','🎵','🎶','🔔','💡','🔑','💎','🚀','🛸','🌍','🌙','☀️','⚽','🏀','🎮','🖥️','📱','📷','🎬','📚','✉️','🔒','🔓'] },
    { label: '🐶', name: 'Animals', emojis: ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐔','🐧','🐦','🦆','🦅','🦉','🦇','🐺','🐴','🦄','🐝','🦋','🐌','🐞','🐜','🦎','🐍','🦕','🦖','🦑','🐙','🦈','🐬','🐳','🐋','🐊','🦭'] },
    { label: '🍎', name: 'Food', emojis: ['🍎','🍊','🍋','🍇','🍓','🍒','🍑','🥭','🍍','🥝','🍅','🥥','🥑','🍆','🥕','🌽','🌶️','🥦','🧄','🥜','🍞','🥐','🧀','🥚','🍳','🥞','🥓','🍗','🍖','🌭','🍔','🍟','🍕','🥪','🌮','🌯','🥗','🍜','🍝','🍣','🍱','🍛','🍦','🍧','🍨','🍩','🍪','🎂','🍰','🧁','🍫','🍬','🍭','☕','🧋','🍵','🥤','🍺','🥂','🍷','🍸','🍹'] },
    { label: '⚽', name: 'Activities', emojis: ['⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🎱','🏓','🏸','🥊','🥋','🎯','⛳','🏹','🎣','🤿','🎽','🎿','🛷','🥌','🎮','🕹️','🎲','♟️','🎭','🎨','🎬','🎤','🎧','🎷','🎸','🎹','🎺','🎻','🥁','🎙️'] },
];

// ── Media Panel ────────────────────────────────────────────────────────
let activeMediaTab = null;

function openMediaPanel(tab) {
    if (!activeUserId) return;

    // toggle off if same tab clicked again
    if (activeMediaTab === tab) { closeMediaPanel(); return; }

    activeMediaTab = tab;

    // update tab active state
    document.querySelectorAll('.media-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab)?.classList.add('active');

    const panel = document.getElementById('media-panel');
    const msgArea = document.getElementById('messages-area');
    const inputBar = document.getElementById('chat-input-bar');

    panel.classList.remove('hidden');
    panel.classList.add('flex');
    msgArea.classList.add('hidden');
    if (inputBar) inputBar.classList.add('hidden');

    document.getElementById('media-panel-title').textContent = tab === 'photo' ? 'Photos' : 'Files';

    const grid = document.getElementById('media-grid');
    grid.innerHTML = `<div class="flex items-center justify-center h-full text-gray-400 text-sm">Loading…</div>`;

    fetch(`${MEDIA_BASE}/${activeUserId}/media`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(items => {
        const filtered = items.filter(i => tab === 'photo' ? i.type === 'image' : i.type === 'file');

        if (!filtered.length) {
            grid.innerHTML = `<div class="flex flex-col items-center justify-center h-full gap-3 text-gray-400">
                <svg class="h-12 w-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${tab === 'photo'
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'}
                </svg>
                <p class="text-sm">No ${tab === 'photo' ? 'photos' : 'files'} yet</p>
            </div>`;
            return;
        }

        if (tab === 'photo') {
            grid.innerHTML = `<div class="photo-grid">${
                filtered.map(i => `
                    <div class="relative group" id="media-item-${i.id}">
                        <a href="${i.url}" class="glightbox" data-gallery="chat-photos" data-title="${escHtml(i.name)}" data-description="${escHtml(i.time)}">
                            <img src="${i.url}" alt="${escHtml(i.name)}" title="${escHtml(i.name)}" />
                        </a>
                        <div class="absolute top-1 right-1 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition">
                            <a href="${i.url}" download="${escHtml(i.name)}"
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                                title="Download">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                            </a>
                            ${i.mine ? `
                            <button onclick="deleteMediaItem(${i.id})"
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/80 text-white hover:bg-red-600"
                                title="Delete">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>` : ''}
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 hidden group-hover:block rounded-b-lg bg-black/40 px-1 py-0.5 text-[9px] text-white truncate">${escHtml(i.time)}</div>
                    </div>`
                ).join('')
            }</div>`;
            // init / refresh GLightbox after DOM is updated
            if (window._chatLightbox) window._chatLightbox.destroy();
            window._chatLightbox = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
        } else {
            grid.innerHTML = `<div class="file-list">${
                filtered.map(i => {
                    const ext = (i.name || '').split('.').pop().toUpperCase();
                    return `<div class="flex items-center gap-3 rounded-xl border border-purple-200 bg-purple-600 px-4 py-3" id="media-item-${i.id}">
                        <svg class="h-8 w-8 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">${escHtml(i.name)}</p>
                            <p class="text-xs text-purple-200">${i.time}</p>
                        </div>
                        <span class="flex-shrink-0 rounded bg-white/25 px-1.5 py-0.5 text-[10px] font-bold text-white">${ext}</span>
                        <a href="${i.url}" download="${escHtml(i.name)}"
                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/40 transition"
                            title="Download">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                        </a>
                        ${i.mine ? `
                        <button onclick="deleteMediaItem(${i.id})"
                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-red-400/80 text-white hover:bg-red-500 transition"
                            title="Delete">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>` : ''}
                    </div>`;
                }).join('')
            }</div>`;
        }
    })
    .catch(() => {
        grid.innerHTML = `<div class="flex items-center justify-center h-full text-red-400 text-sm">Failed to load media.</div>`;
    });
}

function closeMediaPanel() {

    document.querySelectorAll('.media-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('media-panel').classList.add('hidden');
    document.getElementById('media-panel').classList.remove('flex');
    document.getElementById('messages-area').classList.remove('hidden');
    const inputBar = document.getElementById('chat-input-bar');
    if (inputBar) inputBar.classList.remove('hidden');
}

function deleteMediaItem(msgId) {
    if (!confirm('Delete this item?')) return;

    fetch(DELETE_BASE + '/' + msgId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(() => {
        // remove from media grid
        document.getElementById('media-item-' + msgId)?.remove();
        // also mark deleted in the chat message list if visible
        markDeleted(msgId);
    })
    .catch(() => alert('Could not delete item.'));
}

let activeEmojiCategory = 0;
let allEmojis = EMOJI_CATEGORIES.flatMap(c => c.emojis);

function buildEmojiTabs() {
    const tabs = document.getElementById('emoji-tabs');
    if (!tabs) return;
    tabs.innerHTML = EMOJI_CATEGORIES.map((c, i) =>
        `<button class="emoji-tab ${i === 0 ? 'active' : ''}" title="${c.name}" onclick="switchEmojiTab(${i})">${c.label}</button>`
    ).join('');
}

function switchEmojiTab(i) {
    activeEmojiCategory = i;
    document.querySelectorAll('.emoji-tab').forEach((t, idx) =>
        t.classList.toggle('active', idx === i));
    renderEmojiGrid(EMOJI_CATEGORIES[i].emojis);
    document.getElementById('emoji-search').value = '';
}

function renderEmojiGrid(emojis) {
    const grid = document.getElementById('emoji-grid');
    if (!grid) return;
    grid.innerHTML = emojis.map(e =>
        `<button class="emoji-btn" onclick="insertEmoji('${e}')">${e}</button>`
    ).join('');
}

function insertEmoji(emoji) {
    const input = document.getElementById('message-input');
    const start = input.selectionStart;
    const end   = input.selectionEnd;
    input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
    input.selectionStart = input.selectionEnd = start + emoji.length;
    input.focus();
    // auto-grow
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 112) + 'px';
}

// Toggle picker
const emojiToggleBtn = document.getElementById('emoji-toggle-btn');
const emojiPicker    = document.getElementById('emoji-picker');

emojiToggleBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isHidden = emojiPicker.classList.contains('hidden');
    emojiPicker.classList.toggle('hidden');
    if (isHidden) {
        buildEmojiTabs();
        renderEmojiGrid(EMOJI_CATEGORIES[0].emojis);
        document.getElementById('emoji-search')?.focus();
    }
});

// Emoji search
document.getElementById('emoji-search')?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (!q) {
        renderEmojiGrid(EMOJI_CATEGORIES[activeEmojiCategory].emojis);
        return;
    }
    // simple filter — show any emoji whose category name matches, or just search all
    const results = allEmojis.filter(e => e.includes(q));
    renderEmojiGrid(results.length ? results : allEmojis.filter((_, i) => i < 40));
});

// Close picker on outside click
document.addEventListener('click', (e) => {
    if (emojiPicker && !emojiPicker.contains(e.target) && e.target !== emojiToggleBtn) {
        emojiPicker.classList.add('hidden');
    }
});
</script>
@endpush
