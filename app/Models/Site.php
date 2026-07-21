<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['organization_id', 'meeting_id', 'uuid', 'title', 'original_filename', 'index_path', 'user_id'])]
class Site extends Model
{
    use BelongsToOrganization, HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            $site->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Meeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function publicUrl(): string
    {
        return asset('storage/sites/'.$this->uuid.'/'.$this->index_path);
    }
}
