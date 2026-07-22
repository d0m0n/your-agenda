<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
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
            'title' => ['required', 'string', 'max:255'],
            'zip_file' => ['required', 'file', 'mimes:zip,pdf,jpg,jpeg,png,gif,webp', 'max:'.(200 * 1024)],
        ];
    }
}
