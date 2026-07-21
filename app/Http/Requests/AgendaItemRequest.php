<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaItemRequest extends FormRequest
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
        $organizationId = $this->user()->organization_id;
        $meeting = $this->route('meeting');

        return [
            'title' => ['required', 'string', 'max:255'],
            'member_id' => [
                'nullable',
                Rule::exists('members', 'id')->where('organization_id', $organizationId),
            ],
            'site_id' => [
                'nullable',
                Rule::exists('sites', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('meeting_id', $meeting?->id),
            ],
        ];
    }
}
