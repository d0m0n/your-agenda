<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage') ?? false;
    }

    /**
     * No automatic quota check here (unlike MaterialRequest): replacing a
     * file frees the old file's bytes, so the quota comparison has to run
     * in the controller after that space is accounted for, not up front
     * against the raw upload size.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required', 'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,gif,webp,txt,csv',
                'max:20480',
            ],
        ];
    }
}
