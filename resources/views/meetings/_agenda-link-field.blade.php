@php
    $selected = $selected ?? '';
@endphp

<x-input-label :value="__('議案データのリンク')" />
<select name="agenda_link" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm">
    <option value="">{{ __('なし') }}</option>
    @if ($sites->isNotEmpty())
        <optgroup label="{{ __('議案ファイル(この会議)') }}">
            @foreach ($sites as $site)
                <option value="site:{{ $site->id }}" @selected($selected === 'site:'.$site->id)>{{ $site->title }}</option>
            @endforeach
        </optgroup>
    @endif
    @if ($materials->isNotEmpty())
        <optgroup label="{{ __('資料置き場(組織共有)') }}">
            @foreach ($materials as $material)
                <option value="material:{{ $material->id }}" @selected($selected === 'material:'.$material->id)>{{ $material->title }}</option>
            @endforeach
        </optgroup>
    @endif
</select>
