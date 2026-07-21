@php
    $m = $member ?? null;
    $value = fn (string $field) => old($field, $m?->{$field});
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="name" :value="__('氏名')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$value('name')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name_kana" :value="__('よみがな')" />
        <x-text-input id="name_kana" name="name_kana" type="text" class="mt-1 block w-full" :value="$value('name_kana')" />
        <x-input-error :messages="$errors->get('name_kana')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name_romaji" :value="__('ローマ字表記')" />
        <x-text-input id="name_romaji" name="name_romaji" type="text" class="mt-1 block w-full" :value="$value('name_romaji')" />
        <x-input-error :messages="$errors->get('name_romaji')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="serial_number" :value="__('通し番号')" />
        <x-text-input id="serial_number" name="serial_number" type="number" min="1" class="mt-1 block w-full"
            :value="old('serial_number', $m?->serial_number ?? $nextSerialNumber)" />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="position_id" :value="__('役職')" />
        <select id="position_id" name="position_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
            <option value="">{{ __('未設定') }}</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', $m?->position_id) == $position->id)>{{ $position->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="birth_date" :value="__('生年月日')" />
        <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="$m?->birth_date?->format('Y-m-d') ?? old('birth_date')" />
        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gender" :value="__('性別')" />
        @php $gender = old('gender', $m?->gender); @endphp
        <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
            <option value="">{{ __('未設定') }}</option>
            <option value="male" @selected($gender === 'male')>{{ __('男性') }}</option>
            <option value="female" @selected($gender === 'female')>{{ __('女性') }}</option>
            <option value="other" @selected($gender === 'other')>{{ __('その他') }}</option>
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="company" :value="__('所属企業')" />
        <x-text-input id="company" name="company" type="text" class="mt-1 block w-full" :value="$value('company')" />
        <x-input-error :messages="$errors->get('company')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('電話番号')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="$value('phone')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('メールアドレス')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$value('email')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="line_id" :value="__('LINE ID')" />
        <x-text-input id="line_id" name="line_id" type="text" class="mt-1 block w-full" :value="$value('line_id')" />
        <x-input-error :messages="$errors->get('line_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="x_account" :value="__('Xアカウント')" />
        <x-text-input id="x_account" name="x_account" type="text" class="mt-1 block w-full" :value="$value('x_account')" />
        <x-input-error :messages="$errors->get('x_account')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="instagram_account" :value="__('Instagramアカウント')" />
        <x-text-input id="instagram_account" name="instagram_account" type="text" class="mt-1 block w-full" :value="$value('instagram_account')" />
        <x-input-error :messages="$errors->get('instagram_account')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="facebook_account" :value="__('Facebookアカウント')" />
        <x-text-input id="facebook_account" name="facebook_account" type="text" class="mt-1 block w-full" :value="$value('facebook_account')" />
        <x-input-error :messages="$errors->get('facebook_account')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tiktok_account" :value="__('TikTokアカウント')" />
        <x-text-input id="tiktok_account" name="tiktok_account" type="text" class="mt-1 block w-full" :value="$value('tiktok_account')" />
        <x-input-error :messages="$errors->get('tiktok_account')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hobby" :value="__('趣味')" />
        <x-text-input id="hobby" name="hobby" type="text" class="mt-1 block w-full" :value="$value('hobby')" />
        <x-input-error :messages="$errors->get('hobby')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="motto" :value="__('座右の銘')" />
        <x-text-input id="motto" name="motto" type="text" class="mt-1 block w-full" :value="$value('motto')" />
        <x-input-error :messages="$errors->get('motto')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="photo" :value="__('顔写真')" />
        @if ($m?->photoUrl())
            <img src="{{ $m->photoUrl() }}" alt="" class="mt-2 h-20 w-20 rounded-full object-cover">
        @endif
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
            class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-md cursor-pointer focus:outline-none" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('jpg / png / webp形式。自動でリサイズされます。') }}</p>
        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
    </div>
</div>
