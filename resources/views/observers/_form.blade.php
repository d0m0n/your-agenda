@php
    $o = $observer ?? null;
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('氏名')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $o?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('ログインID(メールアドレス)')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $o?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$o ? __('新しいパスワード(変更する場合のみ)') : __('パスワード')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $o" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('パスワード(確認)')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $o" />
    </div>
</div>
