<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Carbon\Carbon;
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

    /**
     * ログイン不要で閲覧できる公開次第のURL。public_tokenが未発行の間はnull。
     */
    public function publicUrl(): ?string
    {
        return $this->public_token ? route('public.meetings.show', $this->public_token) : null;
    }

    public function scheduleLabel(): ?string
    {
        if (! $this->held_at) {
            return null;
        }

        $sameDay = $this->ends_at && $this->held_at->isSameDay($this->ends_at);

        return match (true) {
            $this->ends_at && $sameDay => $this->heldAtDateLabel().' '.$this->held_at->format('H:i').' 〜 '.$this->ends_at->format('H:i'),
            (bool) $this->ends_at => $this->heldAtDateLabel().' '.$this->held_at->format('H:i').' 〜 '.self::formatJapaneseDate($this->ends_at).' '.$this->ends_at->format('H:i'),
            default => $this->heldAtDateLabel().' '.$this->held_at->format('H:i'),
        };
    }

    /**
     * held_at's date portion as "YYYY年MM月DD日(曜)", e.g. "2026年07月22日(水)".
     */
    public function heldAtDateLabel(): ?string
    {
        return $this->held_at ? self::formatJapaneseDate($this->held_at) : null;
    }

    private static function formatJapaneseDate(Carbon $date): string
    {
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        return $date->format('Y年m月d日').'('.$weekdays[$date->dayOfWeek].')';
    }
}
