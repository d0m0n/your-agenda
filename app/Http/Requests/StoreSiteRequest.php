<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesStorageQuota;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'zip_file' => ['required', 'file', 'mimes:zip,pdf,jpg,jpeg,png,gif,webp', 'max:'.(200 * 1024)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->enforceStorageQuota($validator, ['zip_file']);
    }
}
