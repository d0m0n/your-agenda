<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesStorageQuota;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationSettingsRequest extends FormRequest
{
    use EnforcesStorageQuota;

    public function authorize(): bool
    {
        return $this->user()?->can('manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'google_calendar_id' => ['nullable', 'string', 'max:255'],
            'header_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'icon_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'show_meetings_pane' => ['sometimes', 'boolean'],
            'show_calendar_pane' => ['sometimes', 'boolean'],
            'show_birthday_pane' => ['sometimes', 'boolean'],
            'show_materials_pane' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->enforceStorageQuota($validator, ['header_image', 'icon_image']);
    }
}
