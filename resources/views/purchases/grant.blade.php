@extends('layouts.dashboard')

@section('title', 'Grant Purchase')
@section('page-title', 'Grant Purchase')

@section('content')
    <div class="bg-white rounded-lg shadow-sm max-w-xl">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Manually grant access</h3>
            <p class="text-sm text-gray-600 mt-1">
                Use this only when a real store purchase was charged but never made it into the
                purchases table (e.g. the customer sent a receipt after a verification failure).
                This creates a completed purchase record for &yen;0 and is logged for audit.
            </p>
        </div>

        <form method="POST" action="{{ route('purchases.grant') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User email</label>
                <input type="email" name="user_email" value="{{ old('user_email') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('user_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="purchase_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="question_set" {{ old('purchase_type') === 'question_set' ? 'selected' : '' }}>Question Set</option>
                    <option value="package" {{ old('purchase_type') === 'package' ? 'selected' : '' }}>Package</option>
                </select>
                @error('purchase_type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Question Set ID</label>
                <input type="number" name="question_set_id" value="{{ old('question_set_id') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('question_set_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Package ID</label>
                <input type="number" name="package_id" value="{{ old('package_id') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('package_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (required, for audit)</label>
                <textarea name="reason" rows="3" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('reason') }}</textarea>
                @error('reason') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700">
                    Grant access
                </button>
                <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection
