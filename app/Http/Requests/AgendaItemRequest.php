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
     * The "議案データのリンク" field is a single dropdown mixing two
     * different kinds of options (this meeting's sites and the
     * organization's shared materials), submitted as "site:{id}" or
     * "material:{id}". Split it into the two real FK fields before
     * validation so the rest of the rules can stay simple and mutually
     * exclusive by construction (only one of the two can ever be set).
     */
    protected function prepareForValidation(): void
    {
        $link = (string) $this->input('agenda_link', '');
        [$type, $id] = str_contains($link, ':') ? explode(':', $link, 2) : [null, null];

        $this->merge([
            'site_id' => $type === 'site' ? $id : null,
            'material_id' => $type === 'material' ? $id : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;
        $meeting = $this->route('meeting');
        $isCreating = $this->route('agendaItem') === null;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'member_id' => [
                'nullable',
                Rule::exists('members', 'id')->where('organization_id', $organizationId),
            ],
            'assignee_name' => ['nullable', 'string', 'max:255'],
            'site_id' => [
                'nullable',
                Rule::exists('sites', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('meeting_id', $meeting?->id),
            ],
            'material_id' => [
                'nullable',
                Rule::exists('materials', 'id')->where('organization_id', $organizationId),
            ],
        ];

        // parent_id is only accepted when creating: re-parenting an existing
        // item isn't supported, and this keeps update() from ever nulling it
        // out just because the edit form doesn't submit the field.
        if ($isCreating) {
            $rules['parent_id'] = [
                'nullable',
                Rule::exists('agenda_items', 'id')
                    ->where('meeting_id', $meeting?->id)
                    ->whereNull('parent_id'),
            ];
        }

        return $rules;
    }
}
