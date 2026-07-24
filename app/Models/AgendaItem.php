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
     * URL for the linked agenda data. Both site and material links go
     * through an authenticated, subscription-gated route: a site's actual
     * file is a static asset on the public disk (no Laravel route/middleware
     * involved), so sites.open exists purely to wrap "opening" it behind
     * the subscribed middleware (see SiteController::open) before
     * redirecting to the real static URL. Materials already go through
     * materials.download, which is subscription-gated too.
     */
    public function linkUrl(): ?string
    {
        if ($this->site) {
            return route('sites.open', $this->site);
        }

        if ($this->material) {
            return route('materials.download', $this->material);
        }

        return null;
    }

    /**
     * linkUrl()の非ログイン公開版。site・material両方とも、この会議の
     * public_tokenに紐づく公開ゲートルートを経由させる(発行元組織が
     * 未契約の場合はいずれも閲覧不可にするため)。
     */
    public function publicLinkUrl(): ?string
    {
        if (! $this->meeting->public_token) {
            return null;
        }

        if ($this->site) {
            return route('public.meetings.sites.open', [
                'meeting' => $this->meeting->public_token,
                'site' => $this->site,
            ]);
        }

        if ($this->material) {
            return route('public.meetings.materials.download', [
                'meeting' => $this->meeting->public_token,
                'material' => $this->material,
            ]);
        }

        return null;
    }
}
