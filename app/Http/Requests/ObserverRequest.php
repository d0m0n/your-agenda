<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ObserverRequest extends FormRequest
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
        $observer = $this->route('observer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($observer),
            ],
            'password' => [
                $observer ? 'nullable' : 'required',
                Password::defaults(),
                'confirmed',
            ],
        ];
    }
}
