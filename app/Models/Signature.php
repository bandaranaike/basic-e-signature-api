<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Signature extends Model
{
    use HasFactory;
    protected $fillable = [
        'public_id',
        'user_id',
        'file',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // UUID for public_id
        static::creating(function ($model) {
            $model->public_id = Str::uuid()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_signature')
            ->withPivot('signed_user_id', 'signed_at')
            ->withTimestamps();
    }
}
