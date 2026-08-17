@extends('layouts.dashboard')

@section('title', 'Attempt Packs')
@section('page-title', 'Attempt Packs')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Pay-Per-Attempt Packs</h3>
                <p class="text-sm text-gray-600 mt-1">Users spend one attempt from their wallet to unlock any paid question set/package session.</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'AttemptPack'))
                <a href="{{ route('attempt-packs.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                    Add Attempt Pack
                </a>
            @endif
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Product Key</th>
                        <th class="text-left py-2">Attempts</th>
                        <th class="text-left py-2">Price</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-center py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attemptPacks as $pack)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 font-semibold text-gray-800">{{ $pack->name }}</td>
                            <td class="py-4"><code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $pack->product_key }}</code></td>
                            <td class="py-4">{{ $pack->attempts_count }}</td>
                            <td class="py-4 font-semibold">&yen;{{ number_format($pack->price, 2) }}</td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $pack->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $pack->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'AttemptPack'))
                                        <a href="{{ route('attempt-packs.edit', $pack->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'AttemptPack'))
                                        <form action="{{ route('attempt-packs.destroy', $pack->id) }}" method="POST" onsubmit="return confirm('Delete this attempt pack?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-500">No attempt packs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
