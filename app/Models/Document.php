<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'file',
        'user_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Generating a UUID for the public_id
        static::creating(function ($model) {
            $model->public_id = Str::uuid()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
