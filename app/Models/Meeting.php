<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organization_id', 'name', 'held_at', 'ends_at', 'location',
    'wifi_ssid', 'wifi_password', 'memo', 'header_image_path',
])]
class Meeting extends Model
{
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * All agenda items belonging to this meeting, both top-level items and
     * their children. Prefer topLevelAgendaItems() for display/creation —
     * this is mainly here for whole-meeting queries (e.g. order bookkeeping).
     *
     * @return HasMany<AgendaItem, $this>
     */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class)->orderBy('order');
    }

    /**
     * @return HasMany<AgendaItem, $this>
     */
    public function topLevelAgendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class)->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Zip議案(sites)は組織全体ではなくこの会議専用にアップロードされたものだけを返す。
     *
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class)->orderBy('title');
    }

    public function headerImageUrl(): ?string
    {
        return $this->header_image_path ? asset('storage/'.$this->header_image_path) : null;
    }

    public function scheduleLabel(): ?string
    {
        if (! $this->held_at) {
            return null;
        }

        $sameDay = $this->ends_at && $this->held_at->isSameDay($this->ends_at);

        return match (true) {
            $this->ends_at && $sameDay => $this->held_at->format('Y-m-d H:i').' 〜 '.$this->ends_at->format('H:i'),
            (bool) $this->ends_at => $this->held_at->format('Y-m-d H:i').' 〜 '.$this->ends_at->format('Y-m-d H:i'),
            default => $this->held_at->format('Y-m-d H:i'),
        };
    }
}
