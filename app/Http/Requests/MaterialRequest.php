<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesStorageQuota;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
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
            'file' => [
                'required', 'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,gif,webp,txt,csv',
                'max:20480',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->enforceStorageQuota($validator, ['file']);
    }
}
