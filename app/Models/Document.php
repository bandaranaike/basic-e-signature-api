<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    protected $keyType = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'file_path',
        'status',
    ];

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the signature requests for the document.
     */
    public function signatureRequests(): HasMany
    {
        return $this->hasMany(SignatureRequest::class);
    }

    /**
     * Get the signature associated with the document.
     */
    public function signature(): HasOne
    {
        return $this->hasOne(Signature::class);
    }
}
