<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesStorageQuota;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class MeetingRequest extends FormRequest
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
            'held_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:held_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'wifi_ssid' => ['nullable', 'string', 'max:255'],
            'wifi_password' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:5000'],
            'header_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->enforceStorageQuota($validator, ['header_image']);
    }
}
