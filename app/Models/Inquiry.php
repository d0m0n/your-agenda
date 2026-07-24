<?php

namespace App\Models;

use App\Enums\InquiryCategory;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'user_id', 'category', 'subject', 'body'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use BelongsToOrganization, HasFactory;

    protected function casts(): array
    {
        return [
            'category' => InquiryCategory::class,
            'handled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isHandled(): bool
    {
        return ! is_null($this->handled_at);
    }
}
