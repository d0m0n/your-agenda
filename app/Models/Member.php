<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'position_id', 'department_id', 'serial_number', 'name', 'name_kana', 'name_romaji',
    'birth_date', 'gender', 'company', 'phone', 'email', 'line_id', 'x_account',
    'instagram_account', 'facebook_account', 'tiktok_account', 'hobby', 'motto', 'photo_path',
])]
class Member extends Model
{
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    /**
     * "{役職}　　{氏名}" (役職未設定なら氏名のみ)。全角スペース2文字で区切る。
     */
    public function nameWithPosition(): string
    {
        return $this->position ? $this->position->name.'　　'.$this->name : $this->name;
    }
}
