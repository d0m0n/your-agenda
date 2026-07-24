@php
    $d = $department ?? null;
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="serial_number" :value="__('通し番号')" />
        <x-text-input id="serial_number" name="serial_number" type="number" min="1" class="mt-1 block w-full"
            :value="old('serial_number', $d?->serial_number ?? $nextSerialNumber)" required autofocus />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" :value="__('部署名')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $d?->name)" placeholder="{{ __('例: 総務委員会') }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
</div>
