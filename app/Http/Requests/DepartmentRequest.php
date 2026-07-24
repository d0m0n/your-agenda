<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
        $department = $this->route('department');

        return [
            'serial_number' => [
                'required', 'integer', 'min:1',
                Rule::unique('departments', 'serial_number')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($department),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'serial_number.unique' => 'この通し番号は既に使用されています。',
            'serial_number.min' => '通し番号は1以上の数字で入力してください。',
        ];
    }
}
