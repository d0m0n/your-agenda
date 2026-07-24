<?php

namespace App\Http\Requests;

use App\Enums\InquiryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 一般ユーザー・オブザーブユーザーの両方が送信できる
        // (super_adminは組織に属さないため対象外)。
        return ! $this->user()?->isSuperAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(InquiryCategory::class)],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.required' => '種類を選択してください。',
            'subject.required' => '件名を入力してください。',
            'body.required' => '内容を入力してください。',
        ];
    }
}
