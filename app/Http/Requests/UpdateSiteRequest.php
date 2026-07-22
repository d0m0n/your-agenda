<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage') ?? false;
    }

    /**
     * No automatic quota check here (unlike StoreSiteRequest): replacing a
     * file frees the old file's bytes, so the quota comparison has to run
     * in the controller after that space is accounted for, not up front
     * against the raw upload size.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'zip_file' => ['required', 'file', 'mimes:zip,pdf,jpg,jpeg,png,gif,webp', 'max:'.(200 * 1024)],
        ];
    }
}
