<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['meeting_id', 'order', 'title', 'member_id', 'assignee_name', 'site_id'])]
class AgendaItem extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Meeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Display label for the assignee: registered members are shown as
     * "{役職} {氏名}", free-typed names are shown as-is.
     */
    public function assigneeLabel(): ?string
    {
        if ($this->member) {
            return trim(($this->member->position?->name.' ').$this->member->name);
        }

        return $this->assignee_name;
    }
}
