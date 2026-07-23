<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['meeting_id', 'parent_id', 'order', 'title', 'member_id', 'assignee_name', 'site_id', 'material_id'])]
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
     * @return BelongsTo<AgendaItem, $this>
     */
    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(AgendaItem::class, 'parent_id');
    }

    /**
     * Sub-items nested one level under this agenda item (e.g. "11. 協議事項"
     * → "01. ●●の件"). Only one level of nesting is supported.
     *
     * @return HasMany<AgendaItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(AgendaItem::class, 'parent_id')->orderBy('order');
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
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Display label for the assignee: registered members are shown as
     * "{役職} {氏名}", free-typed names are shown as-is.
     */
    public function assigneeLabel(): ?string
    {
        if ($this->member) {
            return $this->member->nameWithPosition();
        }

        return $this->assignee_name;
    }

    /**
     * URL for the linked agenda data: a meeting-scoped site (Zip/PDF/image)
     * opens its public storage URL directly, while an organization-wide
     * material goes through the authenticated download route (materials
     * aren't stored on the public disk).
     */
    public function linkUrl(): ?string
    {
        if ($this->site) {
            return $this->site->publicUrl();
        }

        if ($this->material) {
            return route('materials.download', $this->material);
        }

        return null;
    }

    /**
     * linkUrl()の非ログイン公開版。site側は元々公開ディスク上のURLのため
     * そのまま使えるが、material側はログイン必須の通常ルートではなく、
     * この会議のpublic_tokenに紐づく公開ダウンロードルートを使う。
     */
    public function publicLinkUrl(): ?string
    {
        if ($this->site) {
            return $this->site->publicUrl();
        }

        if ($this->material && $this->meeting->public_token) {
            return route('public.meetings.materials.download', [
                'meeting' => $this->meeting->public_token,
                'material' => $this->material,
            ]);
        }

        return null;
    }
}
