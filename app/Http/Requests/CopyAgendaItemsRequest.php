<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyAgendaItemsRequest extends FormRequest
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

        return [
            'source_meeting_id' => [
                'required',
                Rule::exists('meetings', 'id')->where('organization_id', $organizationId),
            ],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => [
                Rule::exists('agenda_items', 'id')
                    ->where('meeting_id', $this->input('source_meeting_id'))
                    ->whereNull('parent_id'),
            ],
        ];
    }
}
