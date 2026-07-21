<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organization_id', 'name', 'held_at', 'location',
    'wifi_ssid', 'wifi_password', 'memo', 'header_image_path',
])]
class Meeting extends Model
{
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AgendaItem, $this>
     */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class)->orderBy('order');
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
}
