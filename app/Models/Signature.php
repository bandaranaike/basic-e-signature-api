<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    use HasFactory;

    protected $keyType = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'document_id',
        'user_id',
        'signature_file_path',
        'signature_hash',
    ];

    /**
     * Get the document associated with the signature.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user that owns the signature.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
