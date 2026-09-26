{{--
    Optional image upload with live preview.
    $name       - file input name (e.g. "image", "option_images[2]")
    $errorKey   - validation error key (e.g. "image", "option_images.2")
    $label      - heading text, or null for none
    $current    - URL of the image already saved, or null
    $removeName - checkbox name used to remove $current, or null
--}}
<div x-data="{ preview: null, remove: false }">
    @if ($label)
        <label class="mb-2 block text-sm font-semibold text-gray-700">
            {{ $label }} <span class="font-normal text-gray-400">(optional)</span>
        </label>
    @endif

    @if ($current)
        <div class="mb-2 flex items-center gap-3" x-show="!preview">
            <img src="{{ $current }}" alt="" class="h-20 rounded-lg border border-gray-200 object-contain"
                :class="remove && 'opacity-30'">
            @if ($removeName)
                <label class="flex items-center gap-2 text-sm text-red-600">
                    <input type="checkbox" name="{{ $removeName }}" value="1" x-model="remove"
                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    Remove image
                </label>
            @endif
        </div>
    @endif

    <div class="flex items-center gap-3">
        <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/gif,image/webp"
            @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-purple-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-purple-700 hover:file:bg-purple-100">
        <img x-show="preview" :src="preview" alt="" x-cloak class="h-16 rounded-lg border border-gray-200 object-contain">
    </div>
    @if ($label)
        <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF or WEBP, up to 4 MB.@if ($current) Choosing a new file replaces the current image.@endif</p>
    @endif
    @error($errorKey)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
