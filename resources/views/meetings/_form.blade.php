@php
    $m = $meeting ?? null;
    $value = fn (string $field) => old($field, $m?->{$field});
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="name" :value="__('会議名')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$value('name')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="held_at" :value="__('開始日時')" />
        <x-text-input id="held_at" name="held_at" type="datetime-local" class="mt-1 block w-full"
            :value="$m?->held_at?->format('Y-m-d\TH:i') ?? old('held_at')" />
        <x-input-error :messages="$errors->get('held_at')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ends_at" :value="__('終了日時')" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full"
            :value="$m?->ends_at?->format('Y-m-d\TH:i') ?? old('ends_at')" />
        <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="location" :value="__('開催場所')" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="$value('location')" />
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="venue_address" :value="__('開催場所の住所')" />
        <x-text-input id="venue_address" name="venue_address" type="text" class="mt-1 block w-full" :value="$value('venue_address')" />
        <x-input-error :messages="$errors->get('venue_address')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="venue_map_url" :value="__('地図URL')" />
        <x-text-input id="venue_map_url" name="venue_map_url" type="text" class="mt-1 block w-full" :value="$value('venue_map_url')" placeholder="https://maps.google.com/..." />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('GoogleマップのURLなどを入力すると、PDF案内文にQRコードが自動で掲載されます。') }}</p>
        <x-input-error :messages="$errors->get('venue_map_url')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="social_event_info" :value="__('懇親会・2次会情報')" />
        <textarea id="social_event_info" name="social_event_info" rows="3"
            class="mt-1 block w-full border-gray-300 dark:border-ink-600 dark:bg-ink-900 dark:text-ink-100 rounded-md shadow-sm focus:border-leather-400 focus:ring-leather-400"
            placeholder="{{ __('例: 懇親会 19:30〜 会費3,000円 / 2次会 未定') }}">{{ $value('social_event_info') }}</textarea>
        <x-input-error :messages="$errors->get('social_event_info')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="recommended_hotel_info" :value="__('推奨ホテル情報')" />
        <textarea id="recommended_hotel_info" name="recommended_hotel_info" rows="3"
            class="mt-1 block w-full border-gray-300 dark:border-ink-600 dark:bg-ink-900 dark:text-ink-100 rounded-md shadow-sm focus:border-leather-400 focus:ring-leather-400">{{ $value('recommended_hotel_info') }}</textarea>
        <x-input-error :messages="$errors->get('recommended_hotel_info')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="wifi_ssid" :value="__('Wi-Fi SSID')" />
        <x-text-input id="wifi_ssid" name="wifi_ssid" type="text" class="mt-1 block w-full" :value="$value('wifi_ssid')" />
        <x-input-error :messages="$errors->get('wifi_ssid')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="wifi_password" :value="__('Wi-Fi パスワード')" />
        <x-text-input id="wifi_password" name="wifi_password" type="text" class="mt-1 block w-full" :value="$value('wifi_password')" />
        <x-input-error :messages="$errors->get('wifi_password')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="memo" :value="__('メモ')" />
        <textarea id="memo" name="memo" rows="4"
            class="mt-1 block w-full border-gray-300 dark:border-ink-600 dark:bg-ink-900 dark:text-ink-100 rounded-md shadow-sm focus:border-leather-400 focus:ring-leather-400">{{ $value('memo') }}</textarea>
        <x-input-error :messages="$errors->get('memo')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="header_image" :value="__('会議ヘッダー画像')" />
        @if ($m?->headerImageUrl())
            <img src="{{ $m->headerImageUrl() }}" alt="" class="mt-2 h-24 w-full max-w-md rounded-md object-cover">
        @endif
        <input id="header_image" name="header_image" type="file" accept="image/jpeg,image/png,image/webp"
            class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('jpg / png / webp形式。自動でリサイズされます。') }}</p>
        <x-input-error :messages="$errors->get('header_image')" class="mt-2" />
    </div>
</div>
