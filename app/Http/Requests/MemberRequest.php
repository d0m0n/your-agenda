<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
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
        $member = $this->route('member');

        return [
            'name' => ['required', 'string', 'max:255'],
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')->where('organization_id', $this->user()->organization_id),
            ],
            'serial_number' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('members', 'serial_number')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($member),
            ],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'name_romaji' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:255'],
            'x_account' => ['nullable', 'string', 'max:255'],
            'instagram_account' => ['nullable', 'string', 'max:255'],
            'facebook_account' => ['nullable', 'string', 'max:255'],
            'tiktok_account' => ['nullable', 'string', 'max:255'],
            'hobby' => ['nullable', 'string', 'max:1000'],
            'motto' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
