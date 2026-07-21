<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'header_image_path', 'icon_image_path', 'google_calendar_id', 'contracted_at', 'plan_status',
    'show_meetings_pane', 'show_calendar_pane', 'show_birthday_pane', 'show_materials_pane',
])]
class Organization extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'contracted_at' => 'date',
            'show_meetings_pane' => 'boolean',
            'show_calendar_pane' => 'boolean',
            'show_birthday_pane' => 'boolean',
            'show_materials_pane' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return HasMany<Member, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * @return HasMany<Meeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
